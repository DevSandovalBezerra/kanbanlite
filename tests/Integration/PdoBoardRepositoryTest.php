<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DTO\BoardDTO;
use App\Repositories\PdoBoardRepository;

final class PdoBoardRepositoryTest extends IntegrationTestCase
{
    private PdoBoardRepository $repository;
    private int $projectId;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PdoBoardRepository($this->pdo);

        // Seed dependency data
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Test Co', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00')");
        $companyId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO users (company_id, name, email, password, status, created_at, updated_at) VALUES ($companyId, 'Test User', 'test@example.com', 'password', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00')");
        $this->userId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at) VALUES ($companyId, 'Test Project', 'Description', {$this->userId}, '2026-01-01 00:00:00', '2026-01-01 00:00:00')");
        $this->projectId = (int) $this->pdo->lastInsertId();
    }

    public function testCanCreateAndFindBoard(): void
    {
        $dto = new BoardDTO(
            projectId: $this->projectId,
            name: 'Development Board',
            createdBy: $this->userId
        );

        $id = $this->repository->create($dto);
        self::assertGreaterThan(0, $id);

        $found = $this->repository->findById($id);
        self::assertNotNull($found);
        self::assertSame('Development Board', $found->name);
        self::assertSame($this->projectId, $found->projectId);
        self::assertSame($this->userId, $found->createdBy);
    }

    public function testCanUpdateBoard(): void
    {
        $dto = new BoardDTO(
            projectId: $this->projectId,
            name: 'Original Name',
            createdBy: $this->userId
        );
        $id = $this->repository->create($dto);

        $toUpdate = new BoardDTO(
            projectId: $this->projectId,
            name: 'Updated Name',
            createdBy: $this->userId,
            id: $id
        );

        $result = $this->repository->update($toUpdate);
        self::assertTrue($result);

        $found = $this->repository->findById($id);
        self::assertSame('Updated Name', $found->name);
    }

    public function testCanDeleteBoard(): void
    {
        $dto = new BoardDTO(
            projectId: $this->projectId,
            name: 'To Delete',
            createdBy: $this->userId
        );
        $id = $this->repository->create($dto);

        $result = $this->repository->delete($id);
        self::assertTrue($result);

        $found = $this->repository->findById($id);
        self::assertNull($found);
    }

    public function testCanFindByProjectId(): void
    {
        $this->repository->create(new BoardDTO($this->projectId, 'Board 1', $this->userId));
        $this->repository->create(new BoardDTO($this->projectId, 'Board 2', $this->userId));

        $boards = $this->repository->findByProjectId($this->projectId);
        self::assertCount(2, $boards);
        self::assertSame('Board 1', $boards[0]->name);
        self::assertSame('Board 2', $boards[1]->name);
    }
}
