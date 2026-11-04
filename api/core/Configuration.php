<?php
require_once __DIR__ . '/RequestSecurity.php';
RequestSecurity::enforce();
require_once __DIR__ . "/Env.php";
Env::load(__DIR__ . "/.env");


# SECURITY & SESSIONS

$sessionLifetime = 8 * 60 * 60; // 8 horas en segundos

ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

// Ruta de la cookie
$sessionRoot = realpath(__DIR__ . '/../..');
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? $sessionRoot);

$sessionPath = '/' . trim(
    str_replace('\\', '/', substr($sessionRoot, strlen($documentRoot))),
    '/'
) . '/';

ini_set('session.cookie_path', $sessionPath === '//' ? '/' : $sessionPath);

// Duración de la cookie y conservación de la sesión
ini_set('session.gc_maxlifetime', (string) $sessionLifetime);

session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => $sessionPath === '//' ? '/' : $sessionPath,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


#   ENVIRONMENT DEFINITIONS
define('COOKIE_NAME', Env::get('COOKIE_NAME'));
define('COOKIE_KEY', Env::get('COOKIE_KEY'));

define("HOST_DB", Env::get('DATABASE_URL'));
define("USER_DB", Env::get('DATABASE_USER'));
define("PASSWORD_DB", Env::get('DATABASE_PSSW'));
define("DATABASE", Env::get('DATABASE_NAME'));
define("CHARSET", Env::get('DATABASE_CHARSET'));
