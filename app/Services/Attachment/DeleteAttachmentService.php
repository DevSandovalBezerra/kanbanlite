<?php

declare(strict_types=1);

namespace App\Services\Attachment;

use App\Repositories\AttachmentRepository;

final class DeleteAttachmentService
{
    public function __construct(
        private readonly AttachmentRepository $repository,
        private readonly string $uploadBasePath,
    ) {
    }

    public function execute(int $attachmentId): bool
    {
        $attachment = $this->repository->findById($attachmentId);

        if ($attachment === null) {
            return false;
        }

        $absolute = rtrim($this->uploadBasePath, '/') . '/' . $attachment->filepath;

        if (is_file($absolute)) {
            @unlink($absolute);
        }

        return $this->repository->delete($attachmentId);
    }
}
