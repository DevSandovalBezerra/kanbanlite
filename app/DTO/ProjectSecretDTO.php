<?php

declare(strict_types=1);

namespace App\DTO;

final class ProjectSecretDTO
{
    public function __construct(
        public ?int $id,
        public int $projectId,
        public string $secretKey,
        public string $secretValueEnc,
        public int $createdBy,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            projectId: (int) $data['project_id'],
            secretKey: (string) $data['secret_key'],
            secretValueEnc: (string) $data['secret_value_enc'],
            createdBy: (int) $data['created_by'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->projectId,
            'secret_key' => $this->secretKey,
            'secret_value_enc' => $this->secretValueEnc,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}

