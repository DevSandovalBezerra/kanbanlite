<?php

declare(strict_types=1);

$env = static function (string $key, ?string $default = null): ?string {
    $value = getenv($key);
    if (is_string($value) && $value !== '') {
        return $value;
    }

    $value = $_ENV[$key] ?? null;
    if (is_string($value) && $value !== '') {
        return $value;
    }

    $value = $_SERVER[$key] ?? null;
    if (is_string($value) && $value !== '') {
        return $value;
    }

    return $default;
};

return [
    'driver' => $env('DB_DRIVER', 'mysql'),
    'host' => $env('DB_HOST', '127.0.0.1'),
    'port' => $env('DB_PORT', '3306'),
    'database' => $env('DB_NAME') ?: ($env('DB_DATABASE') ?: 'brunor90_kanban'),
    'username' => $env('DB_USER') ?: ($env('DB_USERNAME') ?: 'root'),
    'password' => $env('DB_PASS') ?: ($env('DB_PASSWORD') ?: ''),
    'charset' => $env('DB_CHARSET', 'utf8mb4'),
    'sqlite_path' => $env('DB_SQLITE_PATH') ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'database.sqlite'
];
