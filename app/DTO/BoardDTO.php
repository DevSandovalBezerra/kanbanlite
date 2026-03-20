<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeInterface;

final readonly class BoardDTO
{
    public function __construct(
        public int $projectId,
        public string $name,
        public int $createdBy,
        public ?int $id = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            projectId: (int) $data['project_id'],
            name: (string) $data['name'],
            createdBy: (int) $data['created_by'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->projectId,
            'name' => $this->name,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
