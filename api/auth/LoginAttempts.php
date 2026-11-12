<?php

class LoginAttempts {
    private string $directory;
    private $clock;
    private int $limit;
    private bool $resetOnSuccess;
    private const WINDOW = 900;
    private const LOCK_DURATION = 300;

    public function __construct(?string $directory = null, ?callable $clock = null, int $limit = 3, bool $resetOnSuccess = true) {
        if ($limit < 1) throw new InvalidArgumentException('Invalid attempt limit');
        $this->directory = $directory ?? sys_get_temp_dir() . '/sistemascrew-login-' . substr(hash('sha256', __DIR__), 0, 16);
        $this->clock = $clock ?? static fn() => time();
        $this->limit = $limit;
        $this->resetOnSuccess = $resetOnSuccess;
    }

    public function attempt(string $email, callable $verify): array {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Login attempt storage unavailable');
        }
        $key = hash('sha256', mb_strtolower(trim($email), 'UTF-8'));
        $file = fopen($this->directory . '/' . $key . '.json', 'c+');
        if (!$file) throw new RuntimeException('Login attempt storage unavailable');
        try {
            if (!flock($file, LOCK_EX)) throw new RuntimeException('Login attempt lock unavailable');
            $now = ($this->clock)();
            $raw = stream_get_contents($file);
            $state = $raw === '' ? [] : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($state)) throw new RuntimeException('Invalid login attempt state');
            if (($state['locked_until'] ?? 0) > $now) {
                return ['user' => null, 'retry_after' => $state['locked_until'] - $now];
            }
            if (($state['locked_until'] ?? 0) > 0 || $now - ($state['started'] ?? 0) >= self::WINDOW) $state = [];
            $user = $verify();
            if ($user) {
                if ($this->resetOnSuccess) $state = [];
            } else {
                $state['started'] = $state['started'] ?? $now;
                $state['failures'] = ($state['failures'] ?? 0) + 1;
                if ($state['failures'] >= $this->limit) $state['locked_until'] = $now + self::LOCK_DURATION;
            }
            $encoded = json_encode($state, JSON_THROW_ON_ERROR);
            rewind($file);
            if (!ftruncate($file, 0) || fwrite($file, $encoded) !== strlen($encoded) || !fflush($file)) {
                throw new RuntimeException('Login attempt state unavailable');
            }
            return ['user' => $user ?: null, 'retry_after' => max(0, ($state['locked_until'] ?? 0) - $now)];
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }
}
