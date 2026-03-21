<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeInterface;

final readonly class TaskDTO
{
    public function __construct(
        public int $columnId,
        public string $title,
        public string $description,
        public string $priority,
        public string $status,
        public int $position,
        public int $createdBy,
        public ?int $assignedTo = null,
        public ?DateTimeInterface $deadline = null,
        public ?int $storyPoints = null,
        public ?int $id = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $updatedAt = null,
        public array $labels = [],
        public ?string $assigneeName = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            columnId: (int) $data['column_id'],
            title: (string) $data['title'],
            description: (string) $data['description'],
            priority: (string) $data['priority'],
            status: (string) $data['status'],
            position: (int) $data['position'],
            createdBy: (int) $data['created_by'],
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            deadline: isset($data['deadline']) ? new \DateTimeImmutable($data['deadline']) : null,
            storyPoints: isset($data['story_points']) ? (int) $data['story_points'] : null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            labels: $data['labels'] ?? [],
            assigneeName: isset($data['assignee_name']) ? (string) $data['assignee_name'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'column_id'    => $this->columnId,
            'title'        => $this->title,
            'description'  => $this->description,
            'assigned_to'  => $this->assignedTo,
            'priority'     => $this->priority,
            'story_points' => $this->storyPoints,
            'deadline'     => $this->deadline?->format('Y-m-d H:i:s'),
            'status'       => $this->status,
            'position'     => $this->position,
            'labels'       => $this->labels,
            'created_by'   => $this->createdBy,
            'created_at'    => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updatedAt?->format('Y-m-d H:i:s'),
            // null when unassigned; "Usuário removido" when assigned_to points to a deleted user (S10)
            'assignee_name' => $this->assignedTo !== null
                ? ($this->assigneeName ?? 'Usuário removido')
                : null,
        ];
    }
}
