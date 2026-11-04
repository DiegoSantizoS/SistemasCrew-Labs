<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/auth/LoginAttempts.php';
$directory = sys_get_temp_dir() . '/sistemascrew-attempt-test-' . bin2hex(random_bytes(8));
$now = 10000;
$clock = static function() use (&$now) { return $now; };
$calls = 0;
$failure = static function() use (&$calls) { $calls++; return null; };
function checkAttempt($condition, $label) {
    if (!$condition) throw new RuntimeException($label);
    echo 'PASS ' . $label . PHP_EOL;
}
try {
    $limiter = new LoginAttempts($directory, $clock);
    checkAttempt($limiter->attempt('student@example.com', $failure)['retry_after'] === 0, 'first failure');
    checkAttempt($limiter->attempt('student@example.com', $failure)['retry_after'] === 0, 'second failure');
    checkAttempt($limiter->attempt(' STUDENT@example.com ', $failure)['retry_after'] === 300, 'third failure locks normalized account for five minutes');
    $fresh = new LoginAttempts($directory, $clock);
    checkAttempt($fresh->attempt('student@example.com', static fn() => ['id' => 1])['user'] === null, 'new instance cannot bypass lock with correct password');
    checkAttempt($calls === 3, 'only three password checks');
    checkAttempt($fresh->attempt('other@example.com', static fn() => ['id' => 2])['user']['id'] === 2, 'other accounts remain available');
    $now += 120;
    checkAttempt($fresh->attempt('student@example.com', $failure)['retry_after'] === 180, 'remaining lock time');
    $now += 180;
    checkAttempt($fresh->attempt('student@example.com', static fn() => ['id' => 1])['user']['id'] === 1, 'login available after expiry');
    checkAttempt($fresh->attempt('student@example.com', $failure)['retry_after'] === 0, 'successful login resets failures');
    $now += 900;
    checkAttempt($fresh->attempt('student@example.com', $failure)['retry_after'] === 0, 'failure window expires');
    $fresh->attempt('student@example.com', $failure);
    checkAttempt($fresh->attempt('student@example.com', $failure)['retry_after'] === 300, 'fresh window still enforces limit');
    $ipLimiter = new LoginAttempts($directory, $clock, 30, false);
    for ($i = 0; $i < 29; $i++) {
        if ($ipLimiter->attempt('ip:203.0.113.1', static fn() => null)['retry_after'] !== 0) throw new RuntimeException('IP limit too early');
    }
    checkAttempt($ipLimiter->attempt('ip:203.0.113.1', static fn() => ['id' => 1])['user']['id'] === 1, 'valid login before IP threshold');
    checkAttempt($ipLimiter->attempt('ip:203.0.113.1', static fn() => null)['retry_after'] === 300, 'IP failures survive successful logins and account changes');
    checkAttempt($ipLimiter->attempt('ip:203.0.113.2', static fn() => ['id' => 1])['user']['id'] === 1, 'independent IP remains available');
} finally {
    foreach (glob($directory . '/*.json') ?: [] as $file) unlink($file);
    if (is_dir($directory)) rmdir($directory);
}
