<?php

declare(strict_types=1);

namespace App\Services\Comment;

use App\DTO\CommentDTO;
use App\Repositories\CommentRepository;

final class CreateCommentService
{
    public function __construct(private readonly CommentRepository $repository)
    {
    }

    public function execute(int $taskId, int $userId, string $body): int
    {
        $dto = new CommentDTO(
            taskId: $taskId,
            userId: $userId,
            body: trim($body),
        );

        return $this->repository->create($dto);
    }
}
