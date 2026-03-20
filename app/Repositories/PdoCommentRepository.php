<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\CommentDTO;
use PDO;

final class PdoCommentRepository implements CommentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(CommentDTO $comment): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO task_comments (task_id, user_id, body, created_at)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $comment->taskId,
            $comment->userId,
            $comment->body,
            date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByTaskId(int $taskId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT tc.*, u.name AS user_name
             FROM task_comments tc
             JOIN users u ON u.id = tc.user_id
             WHERE tc.task_id = ?
             ORDER BY tc.created_at ASC'
        );
        $stmt->execute([$taskId]);

        return array_map(
            fn (array $row) => CommentDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?CommentDTO
    {
        $stmt = $this->pdo->prepare(
            'SELECT tc.*, u.name AS user_name
             FROM task_comments tc
             JOIN users u ON u.id = tc.user_id
             WHERE tc.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? CommentDTO::fromArray($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM task_comments WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
