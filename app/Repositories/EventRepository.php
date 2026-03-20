<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\EventDTO;

interface EventRepository
{
    public function create(EventDTO $event): int;
    public function findByCompanyId(int $companyId): array;
    public function update(EventDTO $event): bool;
    public function delete(int $id): bool;
}
