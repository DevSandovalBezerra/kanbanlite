<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\TaskDTO;
use App\Repositories\TaskRepository;
use App\Services\Task\CreateTaskService;
use PHPUnit\Framework\TestCase;

final class CreateTaskServiceTest extends TestCase
{
    private TaskRepository $repository;
    private CreateTaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(TaskRepository::class);
        $this->service = new CreateTaskService($this->repository);
    }

    public function testCanCreateTask(): void
    {
        $dto = new TaskDTO(
            columnId: 1,
            title: 'Test',
            description: 'Desc',
            priority: 'medium',
            status: 'active',
            position: 1,
            createdBy: 123
        );

        $this->repository->expects(self::once())
            ->method('create')
            ->with($dto)
            ->willReturn(456);

        $id = $this->service->execute($dto);
        self::assertSame(456, $id);
    }
}
