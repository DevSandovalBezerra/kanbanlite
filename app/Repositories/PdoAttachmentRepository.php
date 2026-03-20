<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\AttachmentDTO;
use PDO;

final class PdoAttachmentRepository implements AttachmentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(AttachmentDTO $attachment): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO task_attachments (task_id, uploaded_by, filename, filepath, mime_type, size_bytes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $attachment->taskId,
            $attachment->uploadedBy,
            $attachment->filename,
            $attachment->filepath,
            $attachment->mimeType,
            $attachment->sizeBytes,
            date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?AttachmentDTO
    {
        $stmt = $this->pdo->prepare('SELECT * FROM task_attachments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? AttachmentDTO::fromArray($row) : null;
    }

    public function findByTaskId(int $taskId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM task_attachments WHERE task_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$taskId]);

        return array_map(
            fn (array $row) => AttachmentDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM task_attachments WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
