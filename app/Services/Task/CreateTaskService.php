<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\DTO\TaskDTO;
use App\Repositories\TaskRepository;

final class CreateTaskService
{
    public function __construct(private readonly TaskRepository $repository)
    {
    }

    public function execute(TaskDTO $task): int
    {
        // Business logic (validation, etc.) can go here
        return $this->repository->create($task);
    }
}
