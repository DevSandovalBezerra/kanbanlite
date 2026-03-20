<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\ColumnDTO;
use App\Repositories\ColumnRepository;
use App\Services\Column\CreateColumnService;
use PHPUnit\Framework\TestCase;

final class CreateColumnServiceTest extends TestCase
{
    private ColumnRepository $repository;
    private CreateColumnService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ColumnRepository::class);
        $this->service = new CreateColumnService($this->repository);
    }

    public function testCanCreateColumn(): void
    {
        $dto = new ColumnDTO(1, 'Col', 1);
        $this->repository->expects(self::once())
            ->method('create')
            ->with($dto)
            ->willReturn(101);

        $id = $this->service->execute($dto);
        self::assertSame(101, $id);
    }
}
