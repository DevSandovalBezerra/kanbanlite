<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DTO\ColumnDTO;
use App\Repositories\PdoColumnRepository;

final class PdoColumnRepositoryTest extends IntegrationTestCase
{
    private PdoColumnRepository $repository;
    private int $boardId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoColumnRepository($this->pdo);

        // Seed dependency data
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Test Co', 'active', NOW(), NOW())");
        $companyId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO users (company_id, name, email, password, status, created_at, updated_at) VALUES ($companyId, 'Test User', 'test@example.com', 'password', 'active', NOW(), NOW())");
        $userId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at) VALUES ($companyId, 'Test Project', 'Description', {$userId}, NOW(), NOW())");
        $projectId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO boards (project_id, name, created_by, created_at, updated_at) VALUES ($projectId, 'Test Board', {$userId}, NOW(), NOW())");
        $this->boardId = (int) $this->pdo->lastInsertId();
    }

    public function testCanCreateAndFindColumn(): void
    {
        $dto = new ColumnDTO($this->boardId, 'To Do', 1);
        $id = $this->repository->create($dto);
        self::assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);
        self::assertNotNull($found);
        self::assertSame('To Do', $found->name);
        self::assertSame(1, $found->position);
    }

    public function testCanUpdatePositions(): void
    {
        $id1 = $this->repository->create(new ColumnDTO($this->boardId, 'C1', 1));
        $id2 = $this->repository->create(new ColumnDTO($this->boardId, 'C2', 2));

        $result = $this->repository->updatePositions([$id2, $id1]);
        self::assertTrue($result);

        $c1 = $this->repository->findById($id1);
        $c2 = $this->repository->findById($id2);

        self::assertSame(2, $c1->position);
        self::assertSame(1, $c2->position);
    }

    public function testCanUpdateName(): void
    {
        $id = $this->repository->create(new ColumnDTO($this->boardId, 'To Do', 1));
        $found = $this->repository->findById($id);
        self::assertNotNull($found);

        $dto = new ColumnDTO(
            boardId: $found->boardId,
            name: 'Backlog',
            position: $found->position,
            id: $found->id,
            createdAt: $found->createdAt,
            updatedAt: $found->updatedAt
        );

        $ok = $this->repository->update($dto);
        self::assertTrue($ok);

        $updated = $this->repository->findById($id);
        self::assertNotNull($updated);
        self::assertSame('Backlog', $updated->name);
    }
}
