<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\CommentDTO;

interface CommentRepository
{
    public function create(CommentDTO $comment): int;

    /** @return CommentDTO[] */
    public function findByTaskId(int $taskId): array;

    public function findById(int $id): ?CommentDTO;

    public function delete(int $id): bool;
}
