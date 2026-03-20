<?php

declare(strict_types=1);

namespace App\Services\Label;

use App\Repositories\LabelRepository;

final class DetachLabelService
{
    public function __construct(private readonly LabelRepository $repository)
    {
    }

    public function execute(int $taskId, int $labelId): bool
    {
        return $this->repository->detach($taskId, $labelId);
    }
}
