<?php

declare(strict_types=1);

namespace App\Services\Checklist;

use App\Repositories\ChecklistRepository;

final class DeleteChecklistItemService
{
    public function __construct(private readonly ChecklistRepository $repository)
    {
    }

    public function execute(int $itemId): bool
    {
        return $this->repository->deleteItem($itemId);
    }
}
