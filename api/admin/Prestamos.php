<?php
require_once __DIR__ . '/../auth/auth.middleWare.php';
require_once __DIR__ . '/../core/Connection.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../helpers/helpers.php';

$user = requirePermission('Prestamos');
$db = new Connection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $sql = "SELECT e.Id_Equipo, e.Codigo_Equipo AS Codigo, e.Descripcion_Equipo AS Descripcion,
                       e.Marca_Equipo AS Marca, e.Modelo_Equipo AS Modelo,
                       c.Nombre_Categoria AS Categoria, u.Nombre_Ubicacion AS Ubicacion,
                       ee.Nombre_Estado_Equipo AS Estado
                FROM Equipo e
                JOIN Categoria c ON c.Id_Categoria=e.Id_Categoria
                JOIN Ubicacion u ON u.Id_Ubicacion=e.Id_Ubicacion
                JOIN Estado_Equipo ee ON ee.id_estado_Equipo=e.Id_Estado_Equipo
                WHERE ee.Nombre_Estado_Equipo='Disponible'
                ORDER BY e.Codigo_Equipo";
        $equipos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        Response::success('Equipos disponibles obtenidos correctamente.', $equipos, 1, ['csrf' => auth_token()]);
    } catch (Throwable $e) {
        error_log('Error Prestamos.php GET: ' . $e->getMessage());
        Response::error('No se pudieron obtener los equipos disponibles.', -500, 500);
    }
}

if ($method !== 'POST') Response::error('Método no permitido.', -405, 405);
auth_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
$data = leerBody();
$equipoId = filter_var($data['equipo_id'] ?? null, FILTER_VALIDATE_INT);
$fecha = trim((string)($data['fecha_vencimiento'] ?? ''));
$motivo = trim((string)($data['motivo'] ?? ''));
if (!$equipoId || !$fecha || strlen($motivo) > 500) Response::error('Completa una fecha válida y un motivo de hasta 500 caracteres.', -422, 422);
$fechaObj = DateTime::createFromFormat('!Y-m-d', $fecha);
if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha || $fechaObj < new DateTime('today')) Response::error('La fecha de devolución debe ser hoy o una fecha futura.', -422, 422);

try {
    $db->beginTransaction();
    $q = $db->prepare("SELECT e.Id_Equipo, e.Id_Estado_Equipo FROM Equipo e JOIN Estado_Equipo ee ON ee.id_estado_Equipo=e.Id_Estado_Equipo WHERE e.Id_Equipo=? AND ee.Nombre_Estado_Equipo='Disponible' AND NOT EXISTS (SELECT 1 FROM Detalle_Prestamo d JOIN Prestamo p ON p.Id_Prestamo=d.Id_Prestamo JOIN Estado_Prestamo ep ON ep.id_estado_prestamo=p.id_estado_prestamo WHERE d.Id_Equipo=e.Id_Equipo AND ep.nombre_estado IN ('Pendiente','Activo','Vencido') AND d.Fecha_Devolucion IS NULL) FOR UPDATE");
    $q->execute([$equipoId]);
    $equipo = $q->fetch(PDO::FETCH_ASSOC);
    if (!$equipo) {
        $db->rollBack();
        Response::error('Este equipo ya no está disponible. Actualiza la lista e inténtalo con otro.', -409, 409);
    }
    $estadoPrestamo = $db->query("SELECT id_estado_prestamo FROM Estado_Prestamo WHERE nombre_estado='Activo' ORDER BY id_estado_prestamo LIMIT 1")->fetchColumn();
    $estadoEquipoPrestado = $db->query("SELECT id_estado_Equipo FROM Estado_Equipo WHERE Nombre_Estado_Equipo='Prestado' ORDER BY id_estado_Equipo LIMIT 1")->fetchColumn();
    if (!$estadoPrestamo || !$estadoEquipoPrestado) throw new RuntimeException('Falta configurar el estado Activo del préstamo o Prestado del equipo.');
    $q = $db->prepare('INSERT INTO Prestamo (Id_Usuario, id_estado_prestamo, vencimiento_fecha_prestamo, motivo_prestamo) VALUES (?, ?, ?, ?)');
    $q->execute([$user['id'], $estadoPrestamo, $fecha . ' 23:59:59', $motivo ?: null]);
    $prestamoId = (int)$db->lastInsertId();
    $q = $db->prepare('INSERT INTO Detalle_Prestamo (Id_Prestamo, Id_Equipo, Id_Estado_Salida) VALUES (?, ?, ?)');
    $q->execute([$prestamoId, $equipoId, $equipo['Id_Estado_Equipo']]);
    $q = $db->prepare('UPDATE Equipo SET Id_Estado_Equipo=? WHERE Id_Equipo=?');
    $q->execute([$estadoEquipoPrestado, $equipoId]);
    $q = $db->prepare("INSERT INTO Historial (Id_Usuario, Id_Prestamo, Id_Equipo, Accion_Historial, Observacion) VALUES (?, ?, ?, 'Préstamo registrado', ?)");
    $q->execute([$user['id'], $prestamoId, $equipoId, $motivo ?: 'Equipo marcado como Prestado al registrar el préstamo.']);
    $db->commit();
    Response::success('Préstamo registrado. El equipo ahora figura como Prestado.', ['id_prestamo' => $prestamoId], 1);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Error Prestamos.php POST: ' . $e->getMessage());
    Response::error('No se pudo registrar la solicitud de préstamo.', -500, 500);
}
