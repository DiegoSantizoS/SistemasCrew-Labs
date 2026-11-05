<?php
require_once __DIR__ . '/../auth/auth.middleWare.php';
require_once __DIR__ . '/../core/Connection.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../helpers/helpers.php';

$user = requirePermission('Prestamos');
$db = new Connection();
$method = $_SERVER['REQUEST_METHOD'];
$esAdmin = $user['role']['name'] === 'Administrador';

if ($method === 'GET') {
    try {
        $q = $db->prepare("SELECT d.Id_Detalle AS detalle_id, p.Id_Prestamo AS prestamo_id,
                                  e.Codigo_Equipo AS codigo, e.Descripcion_Equipo AS descripcion,
                                  c.Nombre_Categoria AS categoria, u.Nombre_Ubicacion AS ubicacion,
                                  p.emision_fecha_prestamo AS fecha_prestamo,
                                  p.vencimiento_fecha_prestamo AS fecha_vencimiento,
                                  ep.nombre_estado AS estado_prestamo,
                                  CONCAT(per.Nombres_Persona, ' ', per.Apellidos_Persona) AS usuario,
                                  d.Fecha_Devolucion AS fecha_devolucion,
                                  er.Nombre_Estado_Equipo AS estado_retorno,
                                  d.Observaciones AS observaciones
                           FROM Prestamo p
                           JOIN Estado_Prestamo ep ON ep.id_estado_prestamo=p.id_estado_prestamo
                           JOIN Detalle_Prestamo d ON d.Id_Prestamo=p.Id_Prestamo
                           JOIN Usuario sol ON sol.Id_Usuario=p.Id_Usuario
                           JOIN Persona per ON per.Id_Persona=sol.Id_Persona
                           JOIN Equipo e ON e.Id_Equipo=d.Id_Equipo
                           JOIN Categoria c ON c.Id_Categoria=e.Id_Categoria
                           JOIN Ubicacion u ON u.Id_Ubicacion=e.Id_Ubicacion
                           LEFT JOIN Estado_Equipo er ON er.id_estado_Equipo=d.Id_Estado_Retorno
                           " . ($esAdmin ? '' : 'WHERE p.Id_Usuario=?') . "
                           ORDER BY p.emision_fecha_prestamo DESC, d.Id_Detalle DESC");
        $q->execute($esAdmin ? [] : [$user['id']]);
        Response::success('Préstamos obtenidos correctamente.', $q->fetchAll(PDO::FETCH_ASSOC), 1, ['csrf' => auth_token(), 'es_admin' => $esAdmin]);
    } catch (Throwable $e) {
        error_log('Error Devoluciones.php GET: ' . $e->getMessage());
        Response::error('No se pudieron obtener tus préstamos.', -500, 500);
    }
}

if ($method !== 'POST') Response::error('Método no permitido.', -405, 405);
if (!$esAdmin) Response::error('Solo un administrador puede registrar devoluciones.', -403, 403);
$data = leerBody();
$detalleId = filter_var($data['detalle_id'] ?? null, FILTER_VALIDATE_INT);
$estadoRetorno = $data['estado_retorno'] ?? '';
$observacionesRaw = $data['observaciones'] ?? '';
if (!is_string($observacionesRaw)) Response::error('Las observaciones no son válidas.', -422, 422);
$observaciones = trim($observacionesRaw);
if (!$detalleId || !is_string($estadoRetorno) || !in_array($estadoRetorno, ['disponible', 'reparacion'], true) || mb_strlen($observaciones) > 255) {
    Response::error('Completa correctamente los datos de la devolución.', -422, 422);
}

try {
    $db->beginTransaction();
    $q = $db->prepare("SELECT d.Id_Detalle, d.Id_Prestamo, d.Id_Equipo, d.Id_Estado_Salida,
                              p.Id_Usuario, p.id_estado_prestamo, e.Codigo_Equipo
                       FROM Detalle_Prestamo d
                       JOIN Prestamo p ON p.Id_Prestamo=d.Id_Prestamo
                       JOIN Equipo e ON e.Id_Equipo=d.Id_Equipo
                       JOIN Estado_Prestamo ep ON ep.id_estado_prestamo=p.id_estado_prestamo
                       WHERE d.Id_Detalle=? AND d.Fecha_Devolucion IS NULL
                         AND ep.nombre_estado IN ('Activo','Vencido')
                       FOR UPDATE");
    $q->execute([$detalleId]);
    $detalle = $q->fetch(PDO::FETCH_ASSOC);
    if (!$detalle) {
        $db->rollBack();
        Response::error('No se encontró un equipo pendiente de devolución asociado a tu cuenta.', -404, 404);
    }

    $nombreEstado = $estadoRetorno === 'disponible' ? 'Disponible' : 'En reparación';
    $q = $db->prepare('SELECT id_estado_Equipo FROM Estado_Equipo WHERE Nombre_Estado_Equipo=? ORDER BY id_estado_Equipo LIMIT 1');
    $q->execute([$nombreEstado]);
    $idEstado = $q->fetchColumn();
    if (!$idEstado) throw new RuntimeException('No está configurado el estado solicitado para el equipo.');

    $q = $db->prepare('UPDATE Detalle_Prestamo SET Id_Estado_Retorno=?, Fecha_Devolucion=NOW(), Observaciones=? WHERE Id_Detalle=? AND Fecha_Devolucion IS NULL');
    $q->execute([$idEstado, $observaciones ?: null, $detalleId]);
    if ($q->rowCount() !== 1) throw new RuntimeException('El detalle ya fue procesado.');
    $q = $db->prepare('UPDATE Equipo SET Id_Estado_Equipo=? WHERE Id_Equipo=?');
    $q->execute([$idEstado, $detalle['Id_Equipo']]);

    $q = $db->prepare("SELECT COUNT(*) FROM Detalle_Prestamo WHERE Id_Prestamo=? AND Fecha_Devolucion IS NULL");
    $q->execute([$detalle['Id_Prestamo']]);
    if ((int)$q->fetchColumn() === 0) {
        $idDevuelto = $db->query("SELECT id_estado_prestamo FROM Estado_Prestamo WHERE nombre_estado='Devuelto' ORDER BY id_estado_prestamo LIMIT 1")->fetchColumn();
        if (!$idDevuelto) throw new RuntimeException('No está configurado el estado Devuelto.');
        $q = $db->prepare('UPDATE Prestamo SET id_estado_prestamo=? WHERE Id_Prestamo=?');
        $q->execute([$idDevuelto, $detalle['Id_Prestamo']]);
    }

    $observacionHistorial = 'Estado de retorno: ' . $nombreEstado . ($observaciones ? '. ' . $observaciones : '');
    $q = $db->prepare("INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Observacion) VALUES (?, ?, ?, 'Devolución registrada', ?)");
    $q->execute([$user['id'], $detalle['Id_Prestamo'], $detalle['Id_Equipo'], $observacionHistorial]);
    $db->commit();
    Response::success('Devolución registrada correctamente.', ['codigo' => $detalle['Codigo_Equipo']], 1);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Error Devoluciones.php POST: ' . $e->getMessage());
    Response::error('No se pudo registrar la devolución.', -500, 500);
}
