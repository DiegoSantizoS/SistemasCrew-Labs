<?php

class RequestSecurity {
    public static function isHttps(array $server): bool {
        return isset($server['HTTPS']) && $server['HTTPS'] !== '' && strtolower((string)$server['HTTPS']) !== 'off';
    }

    public static function allowed(array $server): bool {
        return self::isHttps($server) || in_array($server['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    }

    public static function enforce(): void {
        if (PHP_SAPI === 'cli') return;
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        if (!self::allowed($_SERVER)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
            exit(json_encode(['code' => -403, 'message' => 'El acceso remoto requiere HTTPS.']));
        }
        if (self::isHttps($_SERVER)) header('Strict-Transport-Security: max-age=31536000');
    }
}
