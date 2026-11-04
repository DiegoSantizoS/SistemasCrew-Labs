<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Usuario.php';

function requireAuth(int|array $roles = []): array {
    header('Cache-Control: no-store');
    $user = auth_current_user();
    if (!$user) {
        Response::error('Tu sesión venció. Inicia sesión nuevamente.', -401, 401);
    }
    $allowed = is_int($roles) ? [$roles] : $roles;
    if ($allowed && !in_array($user['role']['id'], $allowed, true)) {
        Response::error('No tienes permiso para realizar esta acción.', -403, 403);
    }
    return $user;
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
