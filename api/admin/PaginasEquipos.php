<?php
require_once("../core/Connection.php");
require_once("../models/PaginasEquipos.php");
require_once("../core/Response.php");

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