<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\BoardDTO;
use PDO;

final class PdoBoardRepository implements BoardRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(BoardDTO $board): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO boards (project_id, name, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?)'
        );

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $board->projectId,
            $board->name,
            $board->createdBy,
            $now,
            $now
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?BoardDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM boards WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return BoardDTO::fromArray($row);
    }

    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM boards WHERE project_id = ? ORDER BY id ASC');
        $stmt->execute([$projectId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row) => BoardDTO::fromArray($row), $rows);
    }

    public function update(BoardDTO $board): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE boards SET name = ?, updated_at = ? WHERE id = ?'
        );

        return $stmt->execute([
            $board->name,
            date('Y-m-d H:i:s'),
            $board->id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM boards WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
