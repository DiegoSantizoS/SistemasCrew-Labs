<?php
require_once("../core/Connection.php");
require_once("../models/Equipo.php");
require_once("../core/Response.php");
require_once("../helpers/helpers.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        try {
            $ultimoid = $_GET['ultimo'] ?? 0;
            $equipo = new Equipo($conection, $response);
            $equipo->getAll($ultimoid);
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
        break;
    case 'POST':
        $codigo = $_POST['codigo'] ?? null;
        $categoria = $_POST['categoria'] ?? null;
        $ubicacion = $_POST['ubicacion'] ?? null;
        $estado = $_POST['estado'] ?? null;
        $marca = $_POST['marca'] ?? null;
        $modelo = $_POST['modelo'] ?? null;
        $serie = $_POST['serie'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $imagen = $_POST['imagen'] ?? null;

        if ( empty(trim($codigo)) || empty(trim($categoria)) || empty(trim($ubicacion)) || empty(trim($estado)) || empty(trim($descripcion))) {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
        }
        try{
            $equipo = new Equipo($conection, $response);
            $equipo->add($_POST);
        } catch (\Throwable $th) {
            $response->error("Error al agregar el equipo", 2002, 400);
            $response->debug(null, $th);
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

            if ( empty(trim($codigo)) || empty(trim($categoria)) || empty(trim($ubicacion)) || empty(trim($estado)) || empty(trim($descripcion))) {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
            }
            try {
            $equipo = new Equipo($conection, $response);
                $equipo->update($_PUT);
            } catch (\Throwable $th) {
                $response->error("Error al actualizar el equipo", 2003, 400);
            }
            break;
   case 'DELETE':
    try {
        $Codigo = $_GET['Codigo'] ?? null;

        if (empty($Codigo) || trim($Codigo) === '') {
            $response->error("Faltan datos obligatorios", 2002, 400);
            die();
        }

        $equipo = new Equipo($conection, $response);
        $equipo->delete([
            'Codigo' => $Codigo
        ]);

    } catch (\Throwable $th) {
        $response->error("Error al eliminar el equipo", 2004, 400);
    }
    break;
}