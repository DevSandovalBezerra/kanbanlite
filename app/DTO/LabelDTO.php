<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class LabelDTO
{
    public function __construct(
        public int $companyId,
        public string $name,
        public string $color,
        public ?int $id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            companyId: (int) $data['company_id'],
            name: (string) $data['name'],
            color: (string) $data['color'],
            id: isset($data['id']) ? (int) $data['id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'company_id' => $this->companyId,
            'name'       => $this->name,
            'color'      => $this->color,
        ];
    }
}
