<?php

declare(strict_types=1);

namespace App\Services\User;

use PDO;

final class CreateUserService
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * Creates a new user. Returns the new user id.
     * Caller must have validated data (email uniqueness, password policy) beforehand.
     */
    public function execute(
        int $companyId,
        string $name,
        string $email,
        string $plainPassword,
        bool $isAdmin,
        int $actorId
    ): int {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT);
        $now  = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            "INSERT INTO users (company_id, name, email, password, status, is_admin, created_at, updated_at)
             VALUES (?, ?, ?, ?, 'active', ?, ?, ?)"
        );
        $stmt->execute([$companyId, $name, strtolower(trim($email)), $hash, $isAdmin ? 1 : 0, $now, $now]);
        $newId = (int) $this->pdo->lastInsertId();

        $this->audit($actorId, 'user_created', $newId, [
            'name'     => $name,
            'email'    => strtolower(trim($email)),
            'is_admin' => $isAdmin,
        ]);

        return $newId;
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
