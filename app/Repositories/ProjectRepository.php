<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectDTO;

interface ProjectRepository
{
    public function create(ProjectDTO $project): int;
    public function findById(int $id): ?ProjectDTO;
    public function findByCompanyId(int $companyId): array;
    public function findByCompanyIdAndUserId(int $companyId, int $userId): array;
    public function belongsToCompany(int $projectId, int $companyId): bool;
    public function update(ProjectDTO $project): bool;
    public function delete(int $id): bool;
}
