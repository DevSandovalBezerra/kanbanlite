<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class CommentDTO
{
    public function __construct(
        public int $taskId,
        public int $userId,
        public string $body,
        public ?int $id = null,
        public ?DateTimeInterface $createdAt = null,
        public ?string $userName = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            taskId: (int) $data['task_id'],
            userId: (int) $data['user_id'],
            body: (string) $data['body'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            userName: isset($data['user_name']) ? (string) $data['user_name'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'task_id'    => $this->taskId,
            'user_id'    => $this->userId,
            'user_name'  => $this->userName,
            'body'       => $this->body,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
