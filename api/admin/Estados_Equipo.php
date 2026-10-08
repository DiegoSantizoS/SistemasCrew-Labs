<?php

require_once("../core/Connection.php");
require_once("../core/Response.php");
require_once("../models/Estados_Equipo.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        try {
            $estado_equipo = new Estados_Equipo($connection, $response);
            $estado_equipo->getAll();
        } catch (\Throwable $th) {
            $response->error("Error al obtener los resultados", 2001, 400);
        }
        break;
}