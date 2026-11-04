<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Usuario.php';

function requireAuth(int|array $roles = []): array {
    header('Cache-Control: no-store');
    $payload = auth_cookie_payload();
    $session = auth_user();
    if (!$payload || !$session || $payload['id'] !== (int)$session['id']) {
        Response::error('Tu sesión venció. Inicia sesión nuevamente.', -401, 401);
    }
    $current = (new Usuario(new Connection()))->identity($payload['id']);
    if (!$current || $current['estado'] !== 'Activo') {
        unset($_SESSION['user'], $_SESSION['auth_cookie_key']);
        auth_clear_cookie();
        Response::error('Tu sesión venció. Inicia sesión nuevamente.', -401, 401);
    }
    $allowed = is_int($roles) ? [$roles] : $roles;
    if ($allowed && !in_array((int)$current['role_id'], $allowed, true)) {
        Response::error('No tienes permiso para realizar esta acción.', -403, 403);
    }
    return [
        'id' => (int)$current['id'],
        'nombre' => $current['nombre'],
        'role' => ['id' => (int)$current['role_id'], 'name' => $current['rol']],
        'exp' => $payload['exp']
    ];
}

function requirePermission(string $module, ?string $action = null): array {
    $user = requireAuth();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $actions = ['GET' => 'Leer', 'POST' => 'Crear', 'PUT' => 'Actualizar', 'DELETE' => 'Eliminar'];
    if (!isset($actions[$method])) {
        Response::error('Método no permitido.', -405, 405);
    }
    $action = $action ?? $actions[$method];
    if (!(new Usuario(new Connection()))->hasPermission($user['id'], $module, $action)) {
        Response::error('No tienes permiso para realizar esta acción.', -403, 403);
    }
    if ($method !== 'GET') auth_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    return $user;
}
