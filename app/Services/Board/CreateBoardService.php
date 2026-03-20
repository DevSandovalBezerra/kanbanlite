<?php

declare(strict_types=1);

namespace App\Services\Board;

use App\DTO\BoardDTO;
use App\Repositories\BoardRepository;

final class CreateBoardService
{
    public function __construct(private readonly BoardRepository $repository)
    {
    }

    public function execute(BoardDTO $board): int
    {
        return $this->repository->create($board);
    }
}
