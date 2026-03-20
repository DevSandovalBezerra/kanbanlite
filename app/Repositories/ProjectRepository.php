<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectDTO;

interface ProjectRepository
{
    public function create(ProjectDTO $project): int;
    public function findById(int $id): ?ProjectDTO;
    public function findByCompanyId(int $companyId): array;
    public function update(ProjectDTO $project): bool;
    public function delete(int $id): bool;
}
