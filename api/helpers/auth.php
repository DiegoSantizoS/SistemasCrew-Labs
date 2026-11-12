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
function auth_user() {
    $user = $_SESSION['user'] ?? null;
    $payload = auth_cookie_payload();
    return $user && $payload && $payload['id'] === (int)$user['id'] ? $user : null;
}
function auth_cookie_name() {
    return is_string(COOKIE_NAME) && COOKIE_NAME !== '' ? COOKIE_NAME : 'SistemasCrewAuth';
}
function auth_cookie_options($expires) {
    $params = session_get_cookie_params();
    return ['expires' => $expires, 'path' => $params['path'], 'domain' => $params['domain'], 'secure' => $params['secure'], 'httponly' => true, 'samesite' => 'Strict'];
}
function auth_issue_cookie($id) {
    $_SESSION['auth_cookie_key'] = bin2hex(random_bytes(32));
    $payload = ['id' => (int)$id, 'exp' => time() + 8 * 60 * 60];
    $serialized = json_encode($payload, JSON_THROW_ON_ERROR);
    $value = base64_encode($serialized) . '|' . hash_hmac('sha256', $serialized, $_SESSION['auth_cookie_key']);
    if (!setcookie(auth_cookie_name(), $value, auth_cookie_options($payload['exp']))) throw new RuntimeException('Cookie unavailable');
    $_COOKIE[auth_cookie_name()] = $value;
}
function auth_cookie_payload() {
    $value = $_COOKIE[auth_cookie_name()] ?? null;
    $key = $_SESSION['auth_cookie_key'] ?? null;
    if (!is_string($value) || !is_string($key) || substr_count($value, '|') !== 1) return null;
    [$encoded, $signature] = explode('|', $value, 2);
    $serialized = base64_decode($encoded, true);
    if ($serialized === false || !hash_equals(hash_hmac('sha256', $serialized, $key), $signature)) return null;
    $payload = json_decode($serialized, true);
    if (!is_array($payload) || !isset($payload['id'], $payload['exp']) || !is_int($payload['id']) || $payload['id'] <= 0 || !is_int($payload['exp']) || $payload['exp'] <= time()) return null;
    return $payload;
}
function auth_clear_cookie() {
    setcookie(auth_cookie_name(), '', auth_cookie_options(time() - 42000));
    unset($_COOKIE[auth_cookie_name()]);
}
function auth_current_user(): ?array {
    $session = auth_user();
    if (!$session) return null;
    require_once __DIR__ . '/../models/Usuario.php';
    $current = (new Usuario(new Connection()))->identity($session['id']);
    if (!$current || $current['estado'] !== 'Activo') {
        unset($_SESSION['user'], $_SESSION['auth_cookie_key']);
        auth_clear_cookie();
        return null;
    }
    $_SESSION['user'] = ['id' => (int)$current['id'], 'nombre' => $current['nombre']];
    return [
        'id' => (int)$current['id'],
        'nombre' => $current['nombre'],
        'role' => ['id' => (int)$current['role_id'], 'name' => $current['rol']],
        'exp' => auth_cookie_payload()['exp']
    ];
}
function auth_require($api = false) {
    header('Cache-Control: no-store');
    $user = auth_current_user();
    if (!$user) {
        if ($api) Response::error('Tu sesión venció. Inicia sesión nuevamente.', -401, 401);
        header('Location: ' . auth_url('login.php')); exit;
    }
    return $user;
}
function auth_admin() {
    require_once __DIR__ . '/../auth/auth.middleWare.php';
    return requirePermission('Equipos');
}
