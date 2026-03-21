<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ColumnDTO;
use PDO;

final class PdoColumnRepository implements ColumnRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(ColumnDTO $column): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO columns (board_id, name, position, created_at, updated_at) VALUES (?, ?, ?, ?, ?)'
        );

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $column->boardId,
            $column->name,
            $column->position,
            $now,
            $now
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?ColumnDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM columns WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return ColumnDTO::fromArray($row);
    }

    public function findByBoardId(int $boardId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM columns WHERE board_id = ? ORDER BY position ASC');
        $stmt->execute([$boardId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row) => ColumnDTO::fromArray($row), $rows);
    }

    public function update(ColumnDTO $column): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE columns SET name = ?, position = ?, updated_at = ? WHERE id = ?'
        );

        return $stmt->execute([
            $column->name,
            $column->position,
            date('Y-m-d H:i:s'),
            $column->id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM columns WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Resolves the project_id for a column by walking column → board → project.
     * Returns null if the column doesn't exist.
     */
    public function resolveProjectId(int $columnId): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.project_id
             FROM columns c
             JOIN boards b ON b.id = c.board_id
             WHERE c.id = ?
             LIMIT 1'
        );
        $stmt->execute([$columnId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['project_id'] : null;
    }

    public function updatePositions(array $orderedIds): bool
    {
        $this->pdo->beginTransaction();
        try {
            // First pass: move to temporary negative positions to avoid unique constraint
            $stmtTemp = $this->pdo->prepare('UPDATE columns SET position = ? WHERE id = ?');
            foreach ($orderedIds as $index => $id) {
                $stmtTemp->execute([($index + 1) * -1, $id]);
            }

            // Second pass: move to final positive positions
            $stmtFinal = $this->pdo->prepare('UPDATE columns SET position = ? WHERE id = ?');
            foreach ($orderedIds as $index => $id) {
                $stmtFinal->execute([$index + 1, $id]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

}
