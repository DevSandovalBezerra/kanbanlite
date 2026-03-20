<?php

declare(strict_types=1);

namespace App\Services\Checklist;

use App\Repositories\ChecklistRepository;

final class AddChecklistItemService
{
    public function __construct(private readonly ChecklistRepository $repository)
    {
    }

    public function execute(int $checklistId, string $body, int $position = 0): int
    {
        $body = trim($body);

        return $this->repository->addItem($checklistId, $body, $position);
    }
}
