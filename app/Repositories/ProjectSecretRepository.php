<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectSecretDTO;

interface ProjectSecretRepository
{
    public function findByProjectId(int $projectId): array;
    public function findById(int $id): ?ProjectSecretDTO;
    public function create(ProjectSecretDTO $secret): int;
    public function update(ProjectSecretDTO $secret): bool;
    public function delete(int $id): bool;
}

