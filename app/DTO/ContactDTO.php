<?php

declare(strict_types=1);

namespace App\DTO;

final class ContactDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $status,
        public ?string $avatar = null
    ) {}
}
