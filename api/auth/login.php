<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/LoginAttempts.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Response::error('Método no permitido.', -405, 405);
header('Cache-Control: no-store');
$d=leerBody();
auth_csrf($d['csrf'] ?? null);
foreach ($d as $key=>$value) if (!is_string($value)) Response::error('Datos no válidos.', -422, 422);
$email=trim($d['email'] ?? ''); $password=$d['password'] ?? '';
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($email)>150 || $password === '' || strlen($password)>4096) Response::error('Correo o contraseña incorrectos.', -401, 401);
try {
    $accountAttempt = null;
    $ipAttempt = (new LoginAttempts(null, null, 30, false))->attempt('ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), static function() use ($email, $password, &$accountAttempt) {
        $accountAttempt = (new LoginAttempts())->attempt($email, static fn() => (new Usuario(new Connection()))->login($email,$password));
        return $accountAttempt['user'];
    });
    $attempt = $accountAttempt ?? $ipAttempt;
    $attempt['retry_after'] = max($attempt['retry_after'], $ipAttempt['retry_after']);
    if ($attempt['retry_after'] > 0) {
        header('Retry-After: ' . $attempt['retry_after']);
        Response::error('Demasiados intentos fallidos. Espera 5 minutos antes de volver a intentarlo.', -429, 429);
    }
    $user = $attempt['user'];
    if (!$user) Response::error('Correo o contraseña incorrectos.', -401, 401);
    session_regenerate_id(true); $_SESSION['user']=$user; $_SESSION['csrf']=bin2hex(random_bytes(32));
    auth_issue_cookie($user['id']);
    Response::success('Sesión iniciada.', ['redirect'=>auth_url('index.php'), 'id'=>$user['id']], 1);
} catch (Throwable $e) {
    unset($_SESSION['user'], $_SESSION['auth_cookie_key']);
    auth_clear_cookie();
    Response::error('No se pudo iniciar sesión. Inténtalo nuevamente.', -500, 500);
}
