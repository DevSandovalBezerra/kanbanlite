<?php

declare(strict_types=1);

namespace App\Services\Column;

use App\DTO\ColumnDTO;
use App\Repositories\ColumnRepository;

final class CreateColumnService
{
    public function __construct(private readonly ColumnRepository $repository)
    {
    }

    public function execute(ColumnDTO $column): int
    {
        return $this->repository->create($column);
    }
}
