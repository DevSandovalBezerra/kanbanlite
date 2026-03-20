<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeInterface;

final readonly class ColumnDTO
{
    public function __construct(
        public int $boardId,
        public string $name,
        public int $position,
        public ?int $id = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            boardId: (int) $data['board_id'],
            name: (string) $data['name'],
            position: (int) $data['position'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'board_id' => $this->boardId,
            'name' => $this->name,
            'position' => $this->position,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
