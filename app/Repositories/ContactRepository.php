<?php

declare(strict_types=1);

namespace App\Repositories;

interface ContactRepository
{
    public function findByCompanyId(int $companyId): array;
}
