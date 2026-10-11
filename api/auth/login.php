<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Usuario.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Response::error('Método no permitido.', -405, 405);
header('Cache-Control: no-store');
$d=leerBody();
auth_csrf($d['csrf'] ?? null);
foreach ($d as $key=>$value) if (!is_string($value)) Response::error('Datos no válidos.', -422, 422);
$email=trim($d['email'] ?? ''); $password=$d['password'] ?? '';
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($email)>150 || $password === '' || strlen($password)>4096) Response::error('Correo o contraseña incorrectos.', -401, 401);
try {
    $user=(new Usuario(new Connection()))->login($email,$password);
    if (!$user) Response::error('Correo o contraseña incorrectos.', -401, 401);
    session_regenerate_id(true); $_SESSION['user']=$user; $_SESSION['csrf']=bin2hex(random_bytes(32));
    Response::success('Sesión iniciada.', ['redirect'=>auth_url('index.php')], 1);
} catch (Throwable $e) { Response::error('No se pudo iniciar sesión. Inténtalo nuevamente.', -500, 500); }
