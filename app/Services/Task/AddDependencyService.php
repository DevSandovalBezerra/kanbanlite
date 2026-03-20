<?php

declare(strict_types=1);

namespace App\Services\Task;

use PDO;

final class AddDependencyService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Links $taskId as depending on $dependsOnId.
     * Returns false if:
     *  - the relationship already exists
     *  - it would create a direct cycle (A→B and B→A)
     *
     * @throws \InvalidArgumentException when task_id === depends_on_id
     */
    public function execute(int $taskId, int $dependsOnId): bool
    {
        if ($taskId === $dependsOnId) {
            throw new \InvalidArgumentException('Uma tarefa não pode depender de si mesma.');
        }

        // Detect direct cycle: does dependsOnId already depend on taskId?
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM task_dependencies WHERE task_id = ? AND depends_on_id = ? LIMIT 1'
        );
        $stmt->execute([$dependsOnId, $taskId]);
        if ($stmt->fetchColumn() !== false) {
            return false; // would create a cycle
        }

        $insert = $this->pdo->prepare(
            'INSERT IGNORE INTO task_dependencies (task_id, depends_on_id, created_at) VALUES (?, ?, ?)'
        );

        return $insert->execute([$taskId, $dependsOnId, date('Y-m-d H:i:s')]);
    }
}
