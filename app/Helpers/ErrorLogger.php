<?php

declare(strict_types=1);

namespace App\Helpers;

use Throwable;

final class ErrorLogger
{
    private static bool $registered = false;

    public static function register(string $logsDir): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0775, true);
        }

        $logFile = rtrim($logsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'error-' . date('Y-m-d') . '.log';

        ini_set('log_errors', '1');
        ini_set('error_log', $logFile);
        error_reporting(E_ALL);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            self::write('error', $message, [
                'severity' => $severity,
                'file' => $file,
                'line' => $line
            ]);

            return false;
        });

        set_exception_handler(static function (Throwable $e): void {
            self::write('exception', $e::class . ': ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        register_shutdown_function(static function (): void {
            $e = error_get_last();
            if (!is_array($e)) {
                return;
            }

            $type = (int) ($e['type'] ?? 0);
            if (!in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            self::write('fatal', (string) ($e['message'] ?? 'Fatal error'), [
                'type' => $type,
                'file' => (string) ($e['file'] ?? ''),
                'line' => (int) ($e['line'] ?? 0)
            ]);
        });
    }

    public static function write(string $level, string $message, array $context = []): void
    {
        $payload = [
            'ts' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context + self::requestContext()
        ];

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            $encoded = '{"ts":"' . date('c') . '","level":"error","message":"failed to json encode log payload"}';
        }

        error_log($encoded);
    }

    private static function requestContext(): array
    {
        if (PHP_SAPI === 'cli') {
            return ['sapi' => 'cli'];
        }

        return [
            'sapi' => PHP_SAPI,
            'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
            'uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
            'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? '')
        ];
    }
}

