<?php

declare(strict_types=1);

namespace App\DTO;

final class EventDTO
{
    public function __construct(
        public ?int $id,
        public int $companyId,
        public ?int $projectId,
        public string $title,
        public ?string $description,
        public string $startTime,
        public string $endTime,
        public int $createdBy,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            projectId: isset($data['project_id']) ? (int) $data['project_id'] : null,
            title: (string) $data['title'],
            description: $data['description'] ?? null,
            startTime: (string) $data['start_time'],
            endTime: (string) $data['end_time'],
            createdBy: (int) $data['created_by'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }
}
