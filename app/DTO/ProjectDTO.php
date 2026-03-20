<?php

declare(strict_types=1);

namespace App\DTO;

final class ProjectDTO
{
    public function __construct(
        public ?int $id,
        public int $companyId,
        public string $name,
        public string $description,
        public int $createdBy,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            name: (string) $data['name'],
            description: (string) $data['description'],
            createdBy: (int) $data['created_by'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
