<?php
require_once __DIR__ . '/../helpers/auth.php';
auth_admin();
require_once(__DIR__ . "/../core/Connection.php");
require_once(__DIR__ . "/../models/PaginasEquipos.php");
require_once(__DIR__ . "/../core/Response.php");

$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case "GET":
        try{
            $Paginas = new PaginasEquipos($connection, $response);
            $Paginas->getall();
        }
        catch(\Throwable $th){
            $response->error("Error al obtener los resultados", 2001, 400);
            #$response->debug(null, $th);
        }
}