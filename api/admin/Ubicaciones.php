<?php

Require_once("../core/Connection.php");
Require_Once("../core/Response.php");
Require_once("../models/Ubicaciones.php");

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
