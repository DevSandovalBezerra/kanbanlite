<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PdoHistoryRepository implements HistoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function log(
        int $taskId,
        string $action,
        string $oldValue,
        string $newValue,
        int $userId
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO task_history (task_id, action, old_value, new_value, user_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $taskId,
            $action,
            $oldValue,
            $newValue,
            $userId,
            date('Y-m-d H:i:s'),
        ]);
    }

    public function findByTaskId(int $taskId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT th.*, u.name AS user_name
             FROM task_history th
             LEFT JOIN users u ON u.id = th.user_id
             WHERE th.task_id = ?
             ORDER BY th.created_at ASC'
        );
        $stmt->execute([$taskId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
