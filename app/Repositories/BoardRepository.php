<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\BoardDTO;

interface BoardRepository
{
    public function create(BoardDTO $board): int;
    public function findById(int $id): ?BoardDTO;
    public function findByProjectId(int $projectId): array;
    public function update(BoardDTO $board): bool;
    public function delete(int $id): bool;
}
