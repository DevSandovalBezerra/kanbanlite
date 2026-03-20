<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ColumnDTO;

interface ColumnRepository
{
    public function create(ColumnDTO $column): int;
    public function findById(int $id): ?ColumnDTO;
    public function findByBoardId(int $boardId): array;
    public function update(ColumnDTO $column): bool;
    public function delete(int $id): bool;
    public function updatePositions(array $orderedIds): bool;
}
