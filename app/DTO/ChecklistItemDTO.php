<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ChecklistItemDTO
{
    public function __construct(
        public int $checklistId,
        public string $body,
        public bool $isDone,
        public int $position,
        public ?int $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            checklistId: (int) $data['checklist_id'],
            body: (string) $data['body'],
            isDone: (bool) $data['is_done'],
            position: (int) $data['position'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'checklist_id' => $this->checklistId,
            'body'         => $this->body,
            'is_done'      => $this->isDone,
            'position'     => $this->position,
            'created_at'   => $this->createdAt,
            'updated_at'   => $this->updatedAt,
        ];
    }
}
