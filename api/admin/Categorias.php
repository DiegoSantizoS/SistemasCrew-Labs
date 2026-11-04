<?php
require_once __DIR__ . '/../helpers/auth.php';
auth_admin();
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../models/Categorias.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../helpers/helpers.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        try {
            $equipo = new Categorias($conection, $response);
            $equipo->getAll();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $categoria = $_POST['categoria'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $activo = $_POST['activo'] ?? null;

        if ( empty(trim($categoria)) || empty(trim($descripcion)) || empty(trim($activo))) {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
        }
        try{
            $equipo = new Categorias($conection, $response);
            $equipo->add($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar la categoría", 2002, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'PUT':
            $_PUT = leerBody();
            $categoria = $_PUT['categoria'] ?? null;
            $descripcion = $_PUT['descripcion'] ?? null;
            $activo = $_PUT['activo'] ?? null;

            if ( empty(trim($categoria)) || empty(trim($descripcion)) || empty(trim($activo))) {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
            }
            try {
            $equipo = new Categorias($conection, $response);
                $equipo->update($_PUT);
            } catch (\Throwable $th) {
                $response->error("Error al actualizar la categoría", 2003, 400);
            }
            break;
   case 'DELETE':
    try {
        $Id_Categoria = $_GET['Id_Categoria'] ?? null;

        if (empty($Id_Categoria) || trim($Id_Categoria) === '') {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
        }

        $equipo = new Categorias($conection, $response);
        $equipo->delete([
            'id_categoria' => $Id_Categoria
        ]);

    } catch (\Throwable $th) {
        $response->error("Error al eliminar el equipo", 2004, 400);
    }
    break;
}
