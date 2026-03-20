<?php
require __DIR__ . '/bootstrap/app.php';
$config = require __DIR__ . '/config/database.php';
$pdo = \App\Repositories\PdoConnectionFactory::fromConfig($config);
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $tables);
