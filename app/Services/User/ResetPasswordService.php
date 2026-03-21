<?php

declare(strict_types=1);

namespace App\Services\User;

use PDO;

final class ResetPasswordService
{
    private const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    private const PASSWORD_MSG   = 'A senha deve ter no mínimo 8 caracteres, uma letra maiúscula e um número.';

    public function __construct(private readonly PDO $pdo) {}

    public function execute(int $userId, string $newPassword, int $actorId, int $companyId): array
    {
        if (!preg_match(self::PASSWORD_REGEX, $newPassword)) {
            return ['ok' => false, 'error' => self::PASSWORD_MSG];
        }

        $stmt = $this->pdo->prepare(
            "SELECT id FROM users WHERE id = ? AND company_id = ? LIMIT 1"
        );
        $stmt->execute([$userId, $companyId]);
        if (!$stmt->fetch()) {
            return ['ok' => false, 'error' => 'Usuário não encontrado.'];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->pdo->prepare("UPDATE users SET password = ?, updated_at = ? WHERE id = ?")
            ->execute([$hash, date('Y-m-d H:i:s'), $userId]);

        $this->audit($actorId, 'user_password_reset', $userId, []);

        return ['ok' => true];
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
