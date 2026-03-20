<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Repositories\HistoryRepository;
use App\Repositories\TaskRepository;

final class MoveTaskService
{
    public function __construct(
        private readonly TaskRepository $repository,
        private readonly HistoryRepository $historyRepository,
    ) {
    }

    public function execute(int $taskId, int $toColumnId, int $toPosition, int $userId): bool
    {
        $task = $this->repository->findById($taskId);
        $fromColumnId = $task?->columnId;

        $result = $this->repository->move($taskId, $toColumnId, $toPosition);

        if ($result) {
            $this->historyRepository->log(
                taskId: $taskId,
                action: 'moved',
                oldValue: json_encode(['column_id' => $fromColumnId, 'position' => $task?->position]),
                newValue: json_encode(['column_id' => $toColumnId, 'position' => $toPosition]),
                userId: $userId,
            );
        }

        return $result;
    }
}
