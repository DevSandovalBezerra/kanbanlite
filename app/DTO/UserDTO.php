<?php

declare(strict_types=1);

namespace App\DTO;

final class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public string $name,
        public string $email,
        public readonly string $status,
        public readonly bool $isAdmin,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:        isset($row['id']) ? (int) $row['id'] : null,
            companyId: (int) $row['company_id'],
            name:      (string) $row['name'],
            email:     (string) $row['email'],
            status:    (string) $row['status'],
            isAdmin:   (bool) ($row['is_admin'] ?? false),
            createdAt: $row['created_at'] ?? null,
            updatedAt: $row['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'company_id' => $this->companyId,
            'name'       => $this->name,
            'email'      => $this->email,
            'status'     => $this->status,
            'is_admin'   => $this->isAdmin,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
