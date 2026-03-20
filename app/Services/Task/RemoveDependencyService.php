<?php

declare(strict_types=1);

namespace App\Services\Task;

use PDO;

final class RemoveDependencyService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function execute(int $taskId, int $dependsOnId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM task_dependencies WHERE task_id = ? AND depends_on_id = ?'
        );

        return $stmt->execute([$taskId, $dependsOnId]);
    }
}
