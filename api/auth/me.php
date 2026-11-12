<?php
require_once __DIR__ . '/auth.middleWare.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') Response::error('Método no permitido.', -405, 405);
$user = requireAuth();
Response::success('Usuario autenticado.', $user, 1);
