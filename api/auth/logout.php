<?php
require_once __DIR__ . '/../helpers/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Response::error('Método no permitido.', -405, 405);
auth_csrf($_POST['csrf'] ?? null);
$_SESSION=[];
$p=session_get_cookie_params();
setcookie(session_name(), '', ['expires'=>time()-42000,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>$p['samesite']]);
session_destroy();
header('Location: ' . auth_url('login.php'), true, 303); exit;
