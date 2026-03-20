<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\LabelDTO;
use PDO;

final class PdoLabelRepository implements LabelRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(LabelDTO $label): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO labels (company_id, name, color, created_at)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $label->companyId,
            $label->name,
            $label->color,
            date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?LabelDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM labels WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? LabelDTO::fromArray($row) : null;
    }

    public function findByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM labels WHERE company_id = ? ORDER BY name ASC'
        );
        $stmt->execute([$companyId]);

        return array_map(
            fn (array $row) => LabelDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findByTaskId(int $taskId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.*
             FROM labels l
             JOIN task_labels tl ON tl.label_id = l.id
             WHERE tl.task_id = ?
             ORDER BY l.name ASC'
        );
        $stmt->execute([$taskId]);

        return array_map(
            fn (array $row) => LabelDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM labels WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function attach(int $taskId, int $labelId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO task_labels (task_id, label_id) VALUES (?, ?)'
        );
        return $stmt->execute([$taskId, $labelId]);
    }

    public function detach(int $taskId, int $labelId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM task_labels WHERE task_id = ? AND label_id = ?'
        );
        return $stmt->execute([$taskId, $labelId]);
    }
}
