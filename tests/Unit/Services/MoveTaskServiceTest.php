<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\TaskRepository;
use App\Services\Task\MoveTaskService;
use PHPUnit\Framework\TestCase;

final class MoveTaskServiceTest extends TestCase
{
    private TaskRepository $repository;
    private MoveTaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(TaskRepository::class);
        $this->service = new MoveTaskService($this->repository);
    }

    public function testCanMoveTask(): void
    {
        $taskId = 1;
        $toColumnId = 10;
        $toPosition = 5;

        $this->repository->expects(self::once())
            ->method('move')
            ->with($taskId, $toColumnId, $toPosition)
            ->willReturn(true);

        $result = $this->service->execute($taskId, $toColumnId, $toPosition);
        self::assertTrue($result);
    }
}
