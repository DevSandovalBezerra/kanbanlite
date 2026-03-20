<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class AttachmentDTO
{
    public function __construct(
        public int $taskId,
        public int $uploadedBy,
        public string $filename,
        public string $filepath,
        public string $mimeType,
        public int $sizeBytes,
        public ?int $id = null,
        public ?string $createdAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            taskId: (int) $data['task_id'],
            uploadedBy: (int) $data['uploaded_by'],
            filename: (string) $data['filename'],
            filepath: (string) $data['filepath'],
            mimeType: (string) $data['mime_type'],
            sizeBytes: (int) $data['size_bytes'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: $data['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'task_id'     => $this->taskId,
            'uploaded_by' => $this->uploadedBy,
            'filename'    => $this->filename,
            'filepath'    => $this->filepath,
            'mime_type'   => $this->mimeType,
            'size_bytes'  => $this->sizeBytes,
            'created_at'  => $this->createdAt,
        ];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }
}
