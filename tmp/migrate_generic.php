<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = \App\Repositories\PdoConnectionFactory::fromConfig($config);

$sql = file_get_contents('php://stdin');
if ($sql) {
    try {
        $pdo->exec($sql);
        echo "Migration executed successfully.\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
