<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = \App\Repositories\PdoConnectionFactory::fromConfig($config);

$email = 'admin@movamazon.com.br';
$stmt = $pdo->prepare("SELECT id, company_id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    die("Usuário admin@movamazon.com.br não encontrado. Por favor, crie-o primeiro.\n");
}

$userId = $user['id'];
$companyId = $user['company_id'];

// Criar Projeto para Mova Amazon
$pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at) 
           VALUES ($companyId, 'Mova Amazon - Projeto Alpha', 'Primeiro projeto para testes de fluxo', $userId, NOW(), NOW())");
$projectId = $pdo->lastInsertId();

// Criar Quadro
$pdo->exec("INSERT INTO boards (project_id, name, created_by, created_at, updated_at) 
           VALUES ($projectId, 'Operações Mova', $userId, NOW(), NOW())");
$boardId = $pdo->lastInsertId();

// Criar Colunas
$cols = ['A Fazer', 'Em Progresso', 'Revisão', 'Concluído'];
$columnIds = [];
foreach ($cols as $idx => $name) {
    $pos = $idx + 1;
    $pdo->exec("INSERT INTO columns (board_id, name, position, created_at, updated_at) 
               VALUES ($boardId, '$name', $pos, NOW(), NOW())");
    $columnIds[$name] = $pdo->lastInsertId();
}

$colAFazer = $columnIds['A Fazer'];

// Criar 3 Tarefas para teste
$tasks = [
    ['title' => 'Análise de KPIs Mensais', 'desc' => 'Revisar os números de desempenho do último mês.', 'priority' => 'high'],
    ['title' => 'Revisão de Infraestrutura', 'desc' => 'Verificar estabilidade dos servidores em Manaus.', 'priority' => 'medium'],
    ['title' => 'Planejamento de Marketing', 'desc' => 'Definir próximas campanhas regionais.', 'priority' => 'low']
];

foreach ($tasks as $i => $t) {
    $pos = $i + 1;
    $stmt = $pdo->prepare("INSERT INTO tasks (column_id, title, description, priority, status, position, created_by, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, 'todo', ?, ?, NOW(), NOW())");
    $stmt->execute([$colAFazer, $t['title'], $t['desc'], $t['priority'], $pos, $userId]);
}

echo "Sucesso! Criado Projeto ID: $projectId, Quadro ID: $boardId com 3 tarefas em 'A Fazer'.\n";
echo "Acesse: index.php/boards?id=$boardId\n";
