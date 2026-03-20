<?php

declare(strict_types=1);

namespace App\Services\Label;

use App\Repositories\LabelRepository;

final class AttachLabelService
{
    public function __construct(private readonly LabelRepository $repository)
    {
    }

    /**
     * Attaches a label to a task.
     * Returns false if the label does not exist.
     */
    public function execute(int $taskId, int $labelId): bool
    {
        if ($this->repository->findById($labelId) === null) {
            return false;
        }

        return $this->repository->attach($taskId, $labelId);
    }
}
