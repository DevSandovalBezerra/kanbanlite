<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\AttachmentDTO;

interface AttachmentRepository
{
    public function create(AttachmentDTO $attachment): int;

    public function findById(int $id): ?AttachmentDTO;

    /** @return AttachmentDTO[] */
    public function findByTaskId(int $taskId): array;

    public function delete(int $id): bool;
}
