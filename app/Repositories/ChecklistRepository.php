<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ChecklistDTO;
use App\DTO\ChecklistItemDTO;

interface ChecklistRepository
{
    public function createChecklist(int $taskId, string $title): int;

    /** @return ChecklistDTO[] */
    public function findByTaskId(int $taskId): array;

    public function addItem(int $checklistId, string $body, int $position): int;

    public function toggleItem(int $itemId, bool $isDone): bool;

    public function deleteItem(int $itemId): bool;

    public function deleteChecklist(int $checklistId): bool;

    public function findItemById(int $itemId): ?ChecklistItemDTO;
}
