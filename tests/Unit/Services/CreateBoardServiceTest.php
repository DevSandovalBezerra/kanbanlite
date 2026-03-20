<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\BoardDTO;
use App\Repositories\BoardRepository;
use App\Services\Board\CreateBoardService;
use PHPUnit\Framework\TestCase;

final class CreateBoardServiceTest extends TestCase
{
    private BoardRepository $repository;
    private CreateBoardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(BoardRepository::class);
        $this->service = new CreateBoardService($this->repository);
    }

    public function testCanCreateBoard(): void
    {
        $dto = new BoardDTO(1, 'Board', 123);
        $this->repository->expects(self::once())
            ->method('create')
            ->with($dto)
            ->willReturn(789);

        $id = $this->service->execute($dto);
        self::assertSame(789, $id);
    }
}
