<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\TaskDTO;

interface TaskRepository
{
    public function create(TaskDTO $task): int;
    public function findById(int $id): ?TaskDTO;
    public function findByColumnId(int $columnId): array;
    public function update(TaskDTO $task): bool;
    public function delete(int $id): bool;
    public function move(int $taskId, int $toColumnId, int $toPosition): bool;
    public function reorder(int $columnId, array $orderedIds): bool;
}
