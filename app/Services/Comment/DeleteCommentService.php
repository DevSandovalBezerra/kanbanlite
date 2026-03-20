<?php

declare(strict_types=1);

namespace App\Services\Comment;

use App\Repositories\CommentRepository;

final class DeleteCommentService
{
    public function __construct(private readonly CommentRepository $repository)
    {
    }

    /**
     * Deletes a comment only if it belongs to the requesting user.
     * Returns false if not found, not owned, or delete fails.
     */
    public function execute(int $commentId, int $requestingUserId): bool
    {
        $comment = $this->repository->findById($commentId);

        if ($comment === null) {
            return false;
        }

        if ($comment->userId !== $requestingUserId) {
            return false;
        }

        return $this->repository->delete($commentId);
    }
}
