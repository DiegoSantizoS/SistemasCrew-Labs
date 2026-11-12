<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../api/core/RequestSecurity.php';
$cases = [
    [['REMOTE_ADDR' => '127.0.0.1'], true],
    [['REMOTE_ADDR' => '::1'], true],
    [['REMOTE_ADDR' => '192.168.1.10'], false],
    [['REMOTE_ADDR' => '203.0.113.1', 'HTTP_HOST' => 'localhost'], false],
    [['REMOTE_ADDR' => '203.0.113.1', 'HTTP_X_FORWARDED_PROTO' => 'https'], false],
    [['REMOTE_ADDR' => '203.0.113.1', 'HTTP_X_FORWARDED_FOR' => '127.0.0.1'], false],
    [['REMOTE_ADDR' => '203.0.113.1', 'HTTPS' => 'on'], true],
    [['REMOTE_ADDR' => '203.0.113.1', 'HTTPS' => 'off'], false],
    [[], false]
];
foreach ($cases as $index => [$server, $expected]) {
    if (RequestSecurity::allowed($server) !== $expected) throw new RuntimeException('Transport case ' . $index);
}
echo 'PASS 9 transport cases: local HTTP, remote HTTPS, rejection of remote HTTP and spoofed headers' . PHP_EOL;
