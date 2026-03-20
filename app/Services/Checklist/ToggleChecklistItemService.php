<?php

declare(strict_types=1);

namespace App\Services\Checklist;

use App\Repositories\ChecklistRepository;

final class ToggleChecklistItemService
{
    public function __construct(private readonly ChecklistRepository $repository)
    {
    }

    public function execute(int $itemId, bool $isDone): bool
    {
        return $this->repository->toggleItem($itemId, $isDone);
    }
}
