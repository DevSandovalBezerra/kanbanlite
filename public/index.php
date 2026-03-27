<?php

declare(strict_types=1);

use App\Helpers\HttpRequest;

$root = dirname(__DIR__);
$logsDir = $root . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0775, true);
}
if (!is_writable($logsDir)) {
    $logsDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0775, true);
    }
}

$logFile = rtrim($logsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'error-' . date('Y-m-d') . '.log';
ini_set('log_errors', '1');
ini_set('error_log', $logFile);
error_reporting(E_ALL);

$autoloadPath = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoloadPath)) {
    require $autoloadPath;
}

if (class_exists(\App\Helpers\ErrorLogger::class)) {
    \App\Helpers\ErrorLogger::register($logsDir);
}

try {
    $router = require $root . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
} catch (\Throwable $e) {
    if (class_exists(\App\Helpers\ErrorLogger::class)) {
        \App\Helpers\ErrorLogger::write('bootstrap_exception', $e::class . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        error_log($e::class . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }

    http_response_code(500);
    header('content-type: text/plain; charset=utf-8');
    echo 'Erro interno.';
    exit;
}

$request = HttpRequest::fromGlobals();
try {
    $response = $router->dispatch($request);
} catch (\Throwable $e) {
    if (class_exists(\App\Helpers\ErrorLogger::class)) {
        \App\Helpers\ErrorLogger::write('exception', $e::class . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        error_log($e::class . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }
    $response = \App\Helpers\HttpResponse::json([
        'error' => [
            'code' => 'internal_error',
            'message' => 'Erro interno.',
            'details' => []
        ]
    ], 500);
}

http_response_code($response->statusCode());
foreach ($response->headers() as $name => $value) {
    if (is_array($value)) {
        foreach ($value as $v) {
            header($name . ': ' . $v, false);
        }
        continue;
    }
    header($name . ': ' . $value, true);
}

echo $response->body();
