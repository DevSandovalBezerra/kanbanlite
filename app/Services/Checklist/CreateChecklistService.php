<?php

declare(strict_types=1);

namespace App\Services\Checklist;

use App\Repositories\ChecklistRepository;

final class CreateChecklistService
{
    public function __construct(private readonly ChecklistRepository $repository)
    {
    }

    public function execute(int $taskId, string $title = 'Checklist'): int
    {
        $title = trim($title) ?: 'Checklist';

        return $this->repository->createChecklist($taskId, $title);
    }
}
