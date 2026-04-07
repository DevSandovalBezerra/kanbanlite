<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';

$config = require dirname(__DIR__) . '/config/database.php';
$pdo = \App\Repositories\PdoConnectionFactory::fromConfig($config);

// Clear existing data (CAUTION)
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE tasks");
$pdo->exec("TRUNCATE TABLE columns");
$pdo->exec("TRUNCATE TABLE boards");
$pdo->exec("TRUNCATE TABLE projects");
$pdo->exec("TRUNCATE TABLE users");
$pdo->exec("TRUNCATE TABLE companies");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Seed
$pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Software Solutions', 'active', NOW(), NOW())");
$companyId = $pdo->lastInsertId();

$pdo->exec("INSERT INTO users (company_id, name, email, password, status, created_at, updated_at) VALUES ($companyId, 'Administrador', 'admin@kanban.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'active', NOW(), NOW())");
$userId = $pdo->lastInsertId();

$pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at) VALUES ($companyId, 'Projeto Principal', 'Desenvolvimento do Sistema Kanban', $userId, NOW(), NOW())");
$projectId = $pdo->lastInsertId();

$pdo->exec("INSERT INTO boards (project_id, name, created_by, created_at, updated_at) VALUES ($projectId, 'Quadro de Desenvolvimento', $userId, NOW(), NOW())");
$boardId = $pdo->lastInsertId();

$pdo->exec("INSERT INTO boards (project_id, name, created_by, created_at, updated_at) VALUES ($projectId, 'Design & UX', $userId, NOW(), NOW())");

// Ensure the creator is member (owner) of the project so that UI lists it
$pdo->exec("INSERT INTO project_members (project_id, user_id, role_in_project, invited_by, accepted_at, created_at) VALUES ($projectId, $userId, 'owner', $userId, NOW(), NOW())");

$cols = ['A Fazer', 'Em Progresso', 'Revisão', 'Concluído'];
foreach ($cols as $idx => $name) {
    $pos = $idx + 1;
    $pdo->exec("INSERT INTO columns (board_id, name, position, created_at, updated_at) VALUES ($boardId, '$name', $pos, NOW(), NOW())");
}

// Add some dummy tasks
$pdo->exec("INSERT INTO tasks (column_id, title, description, priority, status, position, created_by, created_at, updated_at) VALUES (1, 'Definir Requisitos', 'Mapear todas as funcionalidades do MVP', 'high', 'todo', 1, $userId, NOW(), NOW())");
$pdo->exec("INSERT INTO tasks (column_id, title, description, priority, status, position, created_by, created_at, updated_at) VALUES (1, 'Arquitetura de Dados', 'Modelar o banco de dados MySQL', 'medium', 'todo', 2, $userId, NOW(), NOW())");
$pdo->exec("INSERT INTO tasks (column_id, title, description, priority, status, position, created_by, created_at, updated_at) VALUES (2, 'Implementar Auth', 'Sistema de login e registro', 'high', 'in_progress', 1, $userId, NOW(), NOW())");

echo "Seeding completed successfully in Portuguese.\n";
