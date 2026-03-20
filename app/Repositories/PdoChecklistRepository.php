<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ChecklistDTO;
use App\DTO\ChecklistItemDTO;
use PDO;

final class PdoChecklistRepository implements ChecklistRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function createChecklist(int $taskId, string $title): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO task_checklists (task_id, title, created_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$taskId, $title, date('Y-m-d H:i:s')]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByTaskId(int $taskId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM task_checklists WHERE task_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$taskId]);
        $checklists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($checklists)) {
            return [];
        }

        $checklistIds  = array_column($checklists, 'id');
        $itemsMap      = $this->loadItemsForChecklists($checklistIds);

        return array_map(static function (array $row) use ($itemsMap): ChecklistDTO {
            $row['items'] = $itemsMap[$row['id']] ?? [];
            return ChecklistDTO::fromArray($row);
        }, $checklists);
    }

    public function addItem(int $checklistId, string $body, int $position): int
    {
        $now  = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO task_checklist_items (checklist_id, body, is_done, position, created_at, updated_at)
             VALUES (?, ?, 0, ?, ?, ?)'
        );
        $stmt->execute([$checklistId, $body, $position, $now, $now]);

        return (int) $this->pdo->lastInsertId();
    }

    public function toggleItem(int $itemId, bool $isDone): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE task_checklist_items SET is_done = ?, updated_at = ? WHERE id = ?'
        );
        return $stmt->execute([(int) $isDone, date('Y-m-d H:i:s'), $itemId]);
    }

    public function deleteItem(int $itemId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM task_checklist_items WHERE id = ?');
        return $stmt->execute([$itemId]);
    }

    public function deleteChecklist(int $checklistId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM task_checklists WHERE id = ?');
        return $stmt->execute([$checklistId]);
    }

    public function findItemById(int $itemId): ?ChecklistItemDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM task_checklist_items WHERE id = ?');
        $stmt->execute([$itemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? ChecklistItemDTO::fromArray($row) : null;
    }

    /**
     * @param  int[] $checklistIds
     * @return array<int, ChecklistItemDTO[]>
     */
    private function loadItemsForChecklists(array $checklistIds): array
    {
        $placeholders = implode(',', array_fill(0, count($checklistIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT * FROM task_checklist_items
             WHERE checklist_id IN ($placeholders)
             ORDER BY position ASC, id ASC"
        );
        $stmt->execute($checklistIds);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cid = (int) $row['checklist_id'];
            $map[$cid][] = ChecklistItemDTO::fromArray($row);
        }

        return $map;
    }
}
