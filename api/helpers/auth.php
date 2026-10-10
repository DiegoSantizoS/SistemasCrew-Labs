<?php
require_once __DIR__ . '/../core/Configuration.php';
require_once __DIR__ . '/../core/Response.php';
function auth_url($route = '') {
    $root = realpath(__DIR__ . '/../..');
    $doc = realpath($_SERVER['DOCUMENT_ROOT'] ?? $root);
    $prefix = str_replace('\\', '/', substr($root, strlen($doc)));
    return rtrim('/' . trim($prefix, '/'), '/') . '/' . ltrim($route, '/');
}
function auth_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function auth_csrf($token) {
    if (!is_string($token) || !hash_equals(auth_token(), $token)) Response::error('La sesión del formulario venció. Recarga la página.', -403, 403);
}
function auth_user() { return $_SESSION['user'] ?? null; }
function auth_require($api = false) {
    header('Cache-Control: no-store');
    if (!auth_user()) {
        if ($api) Response::error('Tu sesión venció. Inicia sesión nuevamente.', -401, 401);
        header('Location: ' . auth_url('login.php')); exit;
    }
    return auth_user();
}
function auth_admin() {
    $user = auth_require(true);
    require_once __DIR__ . '/../models/Usuario.php';
    $model = new Usuario(new Connection());
    $current = $model->identity($user['id']);
    if (!$current || $current['estado'] !== 'Activo') {
        unset($_SESSION['user']);
        Response::error('Tu sesión venció. Inicia sesión nuevamente.', -401, 401);
    }
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) Response::error('Método no permitido.', -405, 405);
    if ($method !== 'GET') {
        if (!in_array($current['rol'], ['Administrador', 'Encargado'], true)) Response::error('No tienes permiso para modificar el inventario.', -403, 403);
        auth_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    }
}
