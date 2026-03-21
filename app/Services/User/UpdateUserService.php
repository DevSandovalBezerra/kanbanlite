<?php

declare(strict_types=1);

namespace App\Services\User;

use PDO;

final class UpdateUserService
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * Updates name, email and/or is_admin of a user.
     * Only fields present in $data are updated.
     */
    public function execute(int $userId, array $data, int $actorId): bool
    {
        // Fetch current state for audit
        $stmt = $this->pdo->prepare('SELECT name, email, is_admin FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $before = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$before) {
            return false;
        }

        $sets   = [];
        $params = [];

        if (array_key_exists('name', $data)) {
            $sets[]   = 'name = ?';
            $params[] = trim((string) $data['name']);
        }
        if (array_key_exists('email', $data)) {
            $sets[]   = 'email = ?';
            $params[] = strtolower(trim((string) $data['email']));
        }
        if (array_key_exists('is_admin', $data)) {
            $sets[]   = 'is_admin = ?';
            $params[] = $data['is_admin'] ? 1 : 0;
        }

        if (empty($sets)) {
            return true;
        }

        $sets[]   = 'updated_at = ?';
        $params[] = date('Y-m-d H:i:s');
        $params[] = $userId;

        $sql  = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute($params);

        if ($ok) {
            $this->audit($actorId, 'user_updated', $userId, ['before' => $before, 'after' => $data]);
        }

        return $ok;
    }

    private function audit(int $actorId, string $action, ?int $targetId, array $meta): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_audit_log (actor_id, action, target_user_id, meta, created_at)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$actorId, $action, $targetId, json_encode($meta), date('Y-m-d H:i:s')]);
    }
}
