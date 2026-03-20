<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\EventDTO;
use PDO;

final class PdoEventRepository implements EventRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function create(EventDTO $event): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO events (company_id, project_id, title, description, start_time, end_time, created_by, created_at, updated_at)
            VALUES (:company_id, :project_id, :title, :description, :start_time, :end_time, :created_by, NOW(), NOW())
        ");

        $stmt->execute([
            'company_id' => $event->companyId,
            'project_id' => $event->projectId,
            'title' => $event->title,
            'description' => $event->description,
            'start_time' => $event->startTime,
            'end_time' => $event->endTime,
            'created_by' => $event->createdBy,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM events WHERE company_id = :company_id ORDER BY start_time ASC");
        $stmt->execute(['company_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => EventDTO::fromArray($row), $rows);
    }

    public function update(EventDTO $event): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE events 
            SET title = :title, description = :description, start_time = :start_time, end_time = :end_time, updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'start_time' => $event->startTime,
            'end_time' => $event->endTime,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM events WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
