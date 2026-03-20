<?php

declare(strict_types=1);

namespace App\Repositories;

interface HistoryRepository
{
    public function log(
        int $taskId,
        string $action,
        string $oldValue,
        string $newValue,
        int $userId
    ): void;

    /** @return array<int, array<string, mixed>> */
    public function findByTaskId(int $taskId): array;
}
