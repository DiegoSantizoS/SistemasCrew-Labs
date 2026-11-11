<?php
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Connection.php';
require_once __DIR__ . '/../models/Historial.php';
require_once __DIR__ . '/../auth/auth.middleWare.php';

$user = requireAuth();
$connection = new Connection();
$response = new Response();
$method = $_SERVER['REQUEST_METHOD'];

if($method !== 'GET'){
    $response->error("Método no permitido", 405, 405);
}
$pagina = filter_var($_GET['pagina'] ?? '1', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 1000000]
]);
if ($pagina === false) Response::error('Página no válida.', -422, 422);
$esAdmin = $user['role']['name'] === 'Administrador';
try{
    $historial = new Historial($connection, $response);
    $resultado = $historial->getPagina($esAdmin ? null : $user['id'], $pagina);

    $response->success("Historial obtenido correctamente", $resultado['prestamos'], 1, [
        'pagina' => $resultado['pagina'], 'paginas' => $resultado['paginas'],
        'total' => $resultado['total'], 'es_admin' => $esAdmin
    ]);

}catch(\Throwable $th){
    error_log('Error Historial.php: ' . $th->getMessage());
    $response->error("Error al obtener el historial", -500, 500);
}
