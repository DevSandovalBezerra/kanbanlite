<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ChecklistDTO
{
    public function __construct(
        public int $taskId,
        public string $title,
        public ?int $id = null,
        public ?string $createdAt = null,
        public array $items = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            taskId: (int) $data['task_id'],
            title: (string) $data['title'],
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: $data['created_at'] ?? null,
            items: $data['items'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'task_id'    => $this->taskId,
            'title'      => $this->title,
            'created_at' => $this->createdAt,
            'items'      => array_map(
                fn (ChecklistItemDTO $i) => $i->toArray(),
                $this->items
            ),
        ];
    }
}
