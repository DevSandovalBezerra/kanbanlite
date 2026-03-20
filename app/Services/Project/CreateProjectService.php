<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\DTO\ProjectDTO;
use App\Repositories\ProjectRepository;

final class CreateProjectService
{
    public function __construct(private readonly ProjectRepository $repository) {}

    public function execute(ProjectDTO $project): int
    {
        return $this->repository->create($project);
    }
}
