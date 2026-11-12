<?php
require_once __DIR__ . '/../auth/auth.middleWare.php';
$user = requirePermission('Equipos');
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../models/Equipo.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../helpers/helpers.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['codigo']) && (!is_string($_GET['codigo']) || mb_strlen($_GET['codigo']) > 50)) {
            $response->error('Código de equipo no válido.', -422, 422);
        }
        if (isset($_GET['ultimo']) && filter_var($_GET['ultimo'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
            $response->error('Índice de página no válido.', -422, 422);
        }
        if (isset($_GET['buscar']) && (!is_string($_GET['buscar']) || mb_strlen($_GET['buscar']) > 150)) {
            $response->error('Búsqueda no válida. Usa hasta 150 caracteres.', -422, 422);
        }
        try {
            $equipo = new Equipo($conection, $response);

            if (isset($_GET['codigo']) && trim($_GET['codigo']) !== '') {
                // Detalles completos de un equipo
                $equipo->getAll(trim($_GET['codigo']));
            } else {
                // Listado resumido para la tabla
                // Valida también solicitudes directas que no pasan por el formulario.
                $ultimoid = filter_var($_GET['ultimo'] ?? 0, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 0]
                ]);
                if ($ultimoid === false || !is_string($_GET['buscar'] ?? '')) {
                    $response->error("Parámetros de búsqueda no válidos", 2006, 422);
                }
                // El texto de búsqueda es opcional: vacío conserva el listado por páginas.
                $busqueda = trim($_GET['buscar'] ?? '');
                if (mb_strlen($busqueda) > 100) {
                    $response->error("La búsqueda admite hasta 100 caracteres", 2006, 422);
                }
                $equipo->getPreview($ultimoid, $busqueda);
            }
        } catch (\Throwable $th) {
            error_log('Error Equipos.php: ' . $th->getMessage());

            $response->error(
                "Error al obtener los resultados",
                2001,
                400
            );
        }
        break;
    case 'POST':
        foreach (['sufijo', 'categoria', 'ubicacion', 'estado', 'descripcion'] as $campo) {
            if (!isset($_POST[$campo]) || !is_string($_POST[$campo]) || trim($_POST[$campo]) === '') {
                $response->error("Faltan datos obligatorios", 2002, 400);
            }
        }
        if (filter_var($_POST['categoria'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $response->error("Selecciona una categoría válida.", 2002, 422);
        }
        try {
            $equipo = new Equipo($conection, $response);
            $equipo->add($_POST);
        } catch (PDOException $th) {
            error_log('Error Equipos.php POST: ' . $th->getMessage());
            if ((int)($th->errorInfo[1] ?? 0) === 1062) {
                $response->error('Ya existe un equipo con ese codigo o numero de serie.', 2002, 409);
            }
            if ((int)($th->errorInfo[1] ?? 0) === 1452) {
                $response->error('Selecciona una categoria, ubicacion y estado existentes.', 2002, 422);
            }
            if ((int)($th->errorInfo[1] ?? 0) === 1406) {
                $response->error('Un campo supera su longitud permitida. Usa hasta 100 caracteres en marca, modelo y serie, y 255 en descripcion.', 2002, 422);
            }
            $response->error('No se pudo guardar el equipo. Revisa el registro de errores del servidor.', 2002, 500);
        } catch (\Throwable $th) {
            error_log('Error Equipos.php POST: ' . $th->getMessage());
            $response->error("Error al agregar el equipo", 2002, 500);
        }
        break;
    case 'PUT':
        $_PUT = leerBody();
        $codigo = $_PUT['codigo'] ?? null;
        $categoria = $_PUT['categoria'] ?? null;
        $ubicacion = $_PUT['ubicacion'] ?? null;
        $estado = $_PUT['estado'] ?? null;
        $marca = $_PUT['marca'] ?? null;
        $modelo = $_PUT['modelo'] ?? null;
        $serie = $_PUT['serie'] ?? null;
        $descripcion = $_PUT['descripcion'] ?? null;
        $imagen = $_PUT['imagen'] ?? null;

        if (empty(trim($codigo)) || empty(trim($categoria)) || empty(trim($ubicacion)) || empty(trim($estado)) || empty(trim($descripcion))) {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
        }
        try {
            $equipo = new Equipo($conection, $response);
            $equipo->update($_PUT);
        } catch (DomainException $th) {
            $response->error($th->getMessage(), 2003, 409);
        } catch (OutOfBoundsException $th) {
            $response->error($th->getMessage(), 2005, 404);
        } catch (PDOException $th) {
            error_log('Error Equipos.php PUT: ' . $th->getMessage());
            if ((int)($th->errorInfo[1] ?? 0) === 1062) {
                $response->error('Ya existe un equipo con ese codigo o numero de serie.', 2003, 409);
            }
            $response->error('No se pudo actualizar el equipo.', 2003, 500);
        } catch (\Throwable $th) {
            $response->error("Error al actualizar el equipo", 2003, 400);
        }
        break;
    case 'DELETE':
        try {
            $Codigo = $_GET['codigo'] ?? $_GET['Codigo'] ?? null;

            if (empty($Codigo) || trim($Codigo) === '') {
                $response->error("Faltan datos obligatorios", 2002, 400);
                die();
            }

            $equipo = new Equipo($conection, $response);
            $equipo->delete([
                'Codigo' => $Codigo
            ]);
        } catch (DomainException $th) {
            $response->error($th->getMessage(), 2004, 409);
        } catch (OutOfBoundsException $th) {
            $response->error($th->getMessage(), 2005, 404);
        } catch (\Throwable $th) {
            $response->error("Error al eliminar el equipo", 2004, 400);
        }
        break;
    //AÑADIR MÉTODO QUERY
}
