<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DTO\TaskDTO;
use App\Repositories\PdoTaskRepository;

final class PdoTaskRepositoryTest extends IntegrationTestCase
{
    private PdoTaskRepository $repository;
    private int $columnId;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoTaskRepository($this->pdo);

        // Seed dependency data
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Test Co', 'active', NOW(), NOW())");
        $companyId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO users (company_id, name, email, password, status, created_at, updated_at) VALUES ($companyId, 'Test User', 'test@example.com', 'password', 'active', NOW(), NOW())");
        $this->userId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at) VALUES ($companyId, 'Test Project', 'Description', {$this->userId}, NOW(), NOW())");
        $projectId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO boards (project_id, name, created_by, created_at, updated_at) VALUES ($projectId, 'Test Board', {$this->userId}, NOW(), NOW())");
        $boardId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO columns (board_id, name, position, created_at, updated_at) VALUES ($boardId, 'Col 1', 1, NOW(), NOW())");
        $this->columnId = (int) $this->pdo->lastInsertId();
    }

    public function testCanCreateAndFindTask(): void
    {
        $dto = new TaskDTO(
            columnId: $this->columnId,
            title: 'Test Task',
            description: 'Task Desc',
            priority: 'medium',
            status: 'active',
            position: 1,
            createdBy: $this->userId
        );

        $id = $this->repository->create($dto);
        self::assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);
        self::assertNotNull($found);
        self::assertSame('Test Task', $found->title);
    }

    public function testCanMoveTaskAcrossColumns(): void
    {
        $id = $this->repository->create(new TaskDTO($this->columnId, 'T1', 'D', 'm', 'active', 1, $this->userId));
        
        $this->pdo->exec("INSERT INTO columns (board_id, name, position, created_at, updated_at) VALUES (1, 'Col 2', 2, NOW(), NOW())");
        $newColumnId = (int) $this->pdo->lastInsertId();

        $result = $this->repository->move($id, $newColumnId, 5);
        self::assertTrue($result);

        $found = $this->repository->findById($id);
        self::assertSame($newColumnId, $found->columnId);
        self::assertSame(5, $found->position);
    }
}
