<?php
require_once __DIR__ . '/../auth/auth.middleWare.php';
$user = requirePermission('Equipos');

require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../core/Response.php");
require_once(__DIR__ . "/../models/Ubicaciones.php");

$conection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        try {
            $ubicacion = new Ubicaciones($conection, $response);
            $ubicacion->getAll();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
        }
        break;
}
