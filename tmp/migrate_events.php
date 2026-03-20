<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = \App\Repositories\PdoConnectionFactory::fromConfig($config);

$sql = file_get_contents(dirname(__DIR__) . '/database/migrations/mysql/002_events.sql');
$pdo->exec($sql);

echo "Migration 002_events completed.\n";
