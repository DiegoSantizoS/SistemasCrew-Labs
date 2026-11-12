<?php

require_once __DIR__ . '/../auth/auth.middleWare.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/helpers.php';

$operador = requirePermission('Usuarios');
$metodo = $_SERVER['REQUEST_METHOD'];

function validarIdUsuario(mixed $valor): int
{
    if (!is_int($valor) && !is_string($valor)) throw new InvalidArgumentException('Identificador no valido.');
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) throw new InvalidArgumentException('Identificador no valido.');
    return $id;
}

function validarDatosUsuario(array $datos, bool $nuevo): array
{
    foreach ($datos as $valor) {
        if (!is_string($valor) && !is_int($valor)) throw new InvalidArgumentException('Datos no validos.');
    }
    foreach (['nombres' => 100, 'apellidos' => 100, 'carnet' => 50, 'usuario' => 50, 'correo' => 150, 'telefono' => 20] as $campo => $limite) {
        if (isset($datos[$campo]) && !is_string($datos[$campo])) throw new InvalidArgumentException('Revisa el campo ' . $campo . '.');
        $datos[$campo] = trim($datos[$campo] ?? '');
        if (($campo !== 'telefono' && $datos[$campo] === '') || mb_strlen($datos[$campo]) > $limite) {
            throw new InvalidArgumentException('Revisa el campo ' . $campo . ' (maximo ' . $limite . ' caracteres).');
        }
    }
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Introduce un correo valido.');
    $datos['id_rol'] = validarIdUsuario($datos['id_rol'] ?? null);
    $datos['id_estado'] = validarIdUsuario($datos['id_estado'] ?? null);
    $datos['contrasena'] = $datos['contrasena'] ?? '';
    $confirmacion = $datos['confirmacion'] ?? '';
    if (!is_string($datos['contrasena']) || !is_string($confirmacion)) throw new InvalidArgumentException('Contrasena no valida.');
    if ($nuevo || $datos['contrasena'] !== '') {
        if (mb_strlen($datos['contrasena']) < 8 || strlen($datos['contrasena']) > 72 || str_contains($datos['contrasena'], "\0")) {
            throw new InvalidArgumentException('La contrasena debe tener al menos 8 caracteres y como maximo 72 bytes.');
        }
    }
    if ($datos['contrasena'] !== $confirmacion) throw new InvalidArgumentException('Las contrasenas no coinciden.');
    return $datos;
}

try {
    $modeloUsuario = new Usuario(new Connection());

    if ($metodo === 'POST' || $metodo === 'PUT') {
        $datos = leerBody();
        $idUsuario = $metodo === 'PUT' ? validarIdUsuario($datos['id'] ?? null) : null;
        $datos = validarDatosUsuario($datos, $metodo === 'POST');
        $idUsuario = $modeloUsuario->guardarUsuario($datos, $operador['id'], $idUsuario);
        http_response_code($metodo === 'POST' ? 201 : 200);
        Response::success($metodo === 'POST' ? 'Usuario agregado correctamente.' : 'Usuario actualizado correctamente.', ['id' => $idUsuario], 1);
    }

    if ($metodo === 'DELETE') {
        $idUsuario = validarIdUsuario($_GET['id'] ?? null);
        $modeloUsuario->desactivarUsuario($idUsuario, $operador['id']);
        Response::success('Usuario desactivado. Su historial se conserva.', [], 1);
    }

    if ($metodo !== 'GET') Response::error('Metodo no permitido.', -405, 405);

    if (isset($_GET['catalogos'])) {
        Response::success('Catalogos obtenidos.', $modeloUsuario->obtenerCatalogos(), 1);
    }

    if (isset($_GET['id'])) {
        Response::success('Usuario consultado.', $modeloUsuario->obtenerUsuario(validarIdUsuario($_GET['id'])), 1);
    }

    $busqueda = $_GET['buscar'] ?? '';
    if (!is_string($busqueda) || mb_strlen($busqueda) > 100) throw new InvalidArgumentException('La busqueda admite hasta 100 caracteres.');
    $ultimo = filter_var($_GET['ultimo'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $pagina = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($ultimo === false || $pagina === false) throw new InvalidArgumentException('Indice de pagina no valido.');
    $resultado = $modeloUsuario->listarUsuarios(trim($busqueda), $ultimo, $pagina);
    $permisos = [];
    foreach (['crear' => 'Crear', 'editar' => 'Actualizar', 'eliminar' => 'Eliminar'] as $nombre => $accion) {
        $permisos[$nombre] = $modeloUsuario->hasPermission($operador['id'], 'Usuarios', $accion);
    }
    Response::success('Usuarios consultados correctamente.', $resultado['usuarios'], 1, [
        'has_more' => $resultado['has_more'],
        'next_cursor' => $resultado['next_cursor'],
        'paginacion' => $resultado['paginacion'],
        'pagina' => $resultado['pagina'],
        'paginas' => $resultado['paginas'],
        'permisos' => $permisos,
        'id_actual' => $operador['id']
    ]);
} catch (InvalidArgumentException $error) {
    Response::error($error->getMessage(), -422, 422);
} catch (OutOfBoundsException $error) {
    Response::error($error->getMessage(), -404, 404);
} catch (DomainException $error) {
    Response::error($error->getMessage(), -409, 409);
} catch (Throwable $error) {
    Response::error('No se pudo completar la operacion de usuarios.', -500, 500);
}
