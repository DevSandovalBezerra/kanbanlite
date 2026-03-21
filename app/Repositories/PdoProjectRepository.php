<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectDTO;
use PDO;

final class PdoProjectRepository implements ProjectRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function create(ProjectDTO $project): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at)
            VALUES (:company_id, :name, :description, :created_by, NOW(), NOW())
        ");

        $stmt->execute([
            'company_id' => $project->companyId,
            'name' => $project->name,
            'description' => $project->description,
            'created_by' => $project->createdBy,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?ProjectDTO
    {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return ProjectDTO::fromArray($data);
    }

    public function findByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE company_id = :company_id ORDER BY created_at DESC");
        $stmt->execute(['company_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => ProjectDTO::fromArray($row), $rows);
    }

    public function update(ProjectDTO $project): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE projects 
            SET name = :name, description = :description, updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
        ]);
    }

    public function belongsToCompany(int $projectId, int $companyId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM projects WHERE id = ? AND company_id = ? LIMIT 1"
        );
        $stmt->execute([$projectId, $companyId]);
        return (bool) $stmt->fetchColumn();
    }

    public function delete(int $id): bool
    {
        // First delete dependent boards
        $this->pdo->prepare("DELETE FROM boards WHERE project_id = :id")->execute(['id' => $id]);
        
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
