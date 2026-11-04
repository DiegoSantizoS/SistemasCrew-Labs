<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('session.save_path', sys_get_temp_dir());
require_once __DIR__ . '/../api/helpers/auth.php';

function verifyCookie($condition, $label) {
    if (!$condition) throw new RuntimeException($label);
}

try {
    verifyCookie(auth_cookie_payload() === null, 'Missing cookie accepted');
    auth_issue_cookie(42);
    $original = $_COOKIE[auth_cookie_name()];
    verifyCookie(auth_cookie_payload()['id'] === 42, 'User ID missing');
    verifyCookie(auth_cookie_payload()['exp'] > time(), 'Cookie expiry missing');
    $options = auth_cookie_options(time() + 60);
    verifyCookie($options['httponly'] && $options['samesite'] === 'Strict', 'Cookie flags missing');
    [$encoded, $signature] = explode('|', $original);
    $payload = json_decode(base64_decode($encoded), true);
    $payload['id'] = 99;
    $_COOKIE[auth_cookie_name()] = base64_encode(json_encode($payload)) . '|' . $signature;
    verifyCookie(auth_cookie_payload() === null, 'Tampered ID accepted');
    $payload['exp'] = time() - 1;
    $serialized = json_encode($payload);
    $_COOKIE[auth_cookie_name()] = base64_encode($serialized) . '|' . hash_hmac('sha256', $serialized, $_SESSION['auth_cookie_key']);
    verifyCookie(auth_cookie_payload() === null, 'Expired cookie accepted');
    $_COOKIE[auth_cookie_name()] = [];
    verifyCookie(auth_cookie_payload() === null, 'Malformed cookie accepted');
    $_COOKIE[auth_cookie_name()] = $original;
    unset($_SESSION['auth_cookie_key']);
    verifyCookie(auth_cookie_payload() === null, 'Cookie accepted outside its session');
    auth_issue_cookie(42);
    auth_clear_cookie();
    verifyCookie(auth_cookie_payload() === null, 'Logout cookie still available');
    echo "PASS cookie ID, expiry, flags, signature, tampering, session binding and removal\n";
} finally {
    $_SESSION = [];
    session_destroy();
}
