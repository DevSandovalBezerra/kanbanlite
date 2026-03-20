<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\LabelDTO;

interface LabelRepository
{
    public function create(LabelDTO $label): int;

    public function findById(int $id): ?LabelDTO;

    /** @return LabelDTO[] */
    public function findByCompanyId(int $companyId): array;

    /** @return LabelDTO[] */
    public function findByTaskId(int $taskId): array;

    public function delete(int $id): bool;

    public function attach(int $taskId, int $labelId): bool;

    public function detach(int $taskId, int $labelId): bool;
}
