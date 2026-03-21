<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\TaskDTO;
use PDO;

final class PdoTaskRepository implements TaskRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(TaskDTO $task): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tasks (column_id, title, description, assigned_to, priority, story_points, deadline, status, position, created_by, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $task->columnId,
            $task->title,
            $task->description,
            $task->assignedTo,
            $task->priority,
            $task->storyPoints,
            $task->deadline?->format('Y-m-d H:i:s'),
            $task->status,
            $task->position,
            $task->createdBy,
            $now,
            $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?TaskDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['labels'] = $this->loadLabelsForTask($id);

        return TaskDTO::fromArray($row);
    }

    /**
     * Resolves the project_id for a task by walking task → column → board → project.
     * Returns null if the task doesn't exist.
     */
    public function resolveProjectId(int $taskId): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.project_id
             FROM tasks t
             JOIN columns c ON c.id = t.column_id
             JOIN boards  b ON b.id = c.board_id
             WHERE t.id = ?
             LIMIT 1'
        );
        $stmt->execute([$taskId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['project_id'] : null;
    }

    public function findByColumnId(int $columnId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u.name AS assignee_name
             FROM tasks t
             LEFT JOIN users u ON u.id = t.assigned_to AND u.status != 'deleted'
             WHERE t.column_id = ?
             ORDER BY t.position ASC"
        );
        $stmt->execute([$columnId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return [];
        }

        $taskIds    = array_column($rows, 'id');
        $labelsMap  = $this->loadLabelsForTasks($taskIds);

        return array_map(static function (array $row) use ($labelsMap): TaskDTO {
            $row['labels'] = $labelsMap[$row['id']] ?? [];
            return TaskDTO::fromArray($row);
        }, $rows);
    }

    /** @return array<string, mixed> */
    private function loadLabelsForTask(int $taskId): array
    {
        return $this->loadLabelsForTasks([$taskId])[$taskId] ?? [];
    }

    /**
     * Loads labels for multiple tasks in one query.
     * @param  int[] $taskIds
     * @return array<int, array<string, mixed>[]>
     */
    private function loadLabelsForTasks(array $taskIds): array
    {
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT tl.task_id, l.id, l.name, l.color
             FROM task_labels tl
             JOIN labels l ON l.id = tl.label_id
             WHERE tl.task_id IN ($placeholders)
             ORDER BY l.name ASC"
        );
        $stmt->execute($taskIds);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $tid = (int) $row['task_id'];
            $map[$tid][] = [
                'id'    => (int) $row['id'],
                'name'  => $row['name'],
                'color' => $row['color'],
            ];
        }

        return $map;
    }

    public function update(TaskDTO $task): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tasks
             SET column_id = ?, title = ?, description = ?, assigned_to = ?,
                 priority = ?, story_points = ?, deadline = ?, status = ?,
                 position = ?, updated_at = ?
             WHERE id = ?'
        );

        return $stmt->execute([
            $task->columnId,
            $task->title,
            $task->description,
            $task->assignedTo,
            $task->priority,
            $task->storyPoints,
            $task->deadline?->format('Y-m-d H:i:s'),
            $task->status,
            $task->position,
            date('Y-m-d H:i:s'),
            $task->id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function move(int $taskId, int $toColumnId, int $toPosition): bool
    {
        $stmt = $this->pdo->prepare('UPDATE tasks SET column_id = ?, position = ?, updated_at = ? WHERE id = ?');
        return $stmt->execute([
            $toColumnId,
            $toPosition,
            date('Y-m-d H:i:s'),
            $taskId
        ]);
    }

    public function reorder(int $columnId, array $orderedIds): bool
    {
        $this->pdo->beginTransaction();
        try {
            // First pass: temporary negative positions
            $stmtTemp = $this->pdo->prepare('UPDATE tasks SET position = ? WHERE id = ?');
            foreach ($orderedIds as $index => $id) {
                $stmtTemp->execute([($index + 1) * -1, $id]);
            }

            // Second pass: final positions
            $stmtFinal = $this->pdo->prepare('UPDATE tasks SET position = ? WHERE id = ?');
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
