<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectSecretDTO;
use PDO;

final class PdoProjectSecretRepository implements ProjectSecretRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM project_secrets WHERE project_id = ? ORDER BY secret_key ASC'
        );
        $stmt->execute([$projectId]);

        return array_map(
            fn (array $row) => ProjectSecretDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?ProjectSecretDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM project_secrets WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ProjectSecretDTO::fromArray($row) : null;
    }

    public function create(ProjectSecretDTO $secret): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO project_secrets (project_id, secret_key, secret_value_enc, created_by, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $secret->projectId,
            $secret->secretKey,
            $secret->secretValueEnc,
            $secret->createdBy,
            $now,
            $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(ProjectSecretDTO $secret): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE project_secrets
             SET secret_key = ?, secret_value_enc = ?, updated_at = ?
             WHERE id = ?'
        );

        return $stmt->execute([
            $secret->secretKey,
            $secret->secretValueEnc,
            date('Y-m-d H:i:s'),
            $secret->id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM project_secrets WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

