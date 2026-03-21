<?php

declare(strict_types=1);

namespace App\Services\User;

use PDO;

final class ToggleUserStatusService
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * Activates or deactivates a user.
     *
     * Returns ['ok' => false, 'error' => '...'] on business-rule violations,
     * or ['ok' => true] on success.
     */
    public function execute(int $userId, int $actorId, int $companyId): array
    {
        if ($userId === $actorId) {
            return ['ok' => false, 'error' => 'Não é possível alterar o próprio status.'];
        }

        $stmt = $this->pdo->prepare(
            "SELECT status, is_admin FROM users WHERE id = ? AND company_id = ? LIMIT 1"
        );
        $stmt->execute([$userId, $companyId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['ok' => false, 'error' => 'Usuário não encontrado.'];
        }

        $currentStatus = $user['status'];
        $newStatus     = $currentStatus === 'active' ? 'inactive' : 'active';

        // Disabling: check if this is the last active admin
        if ($currentStatus === 'active' && (bool) $user['is_admin']) {
            $countStmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM users WHERE company_id = ? AND is_admin = 1 AND status = 'active'"
            );
            $countStmt->execute([$companyId]);
            if ((int) $countStmt->fetchColumn() <= 1) {
                return ['ok' => false, 'error' => 'Não é possível desativar o único administrador ativo da empresa.'];
            }
        }

        $this->pdo->prepare("UPDATE users SET status = ?, updated_at = ? WHERE id = ?")
            ->execute([$newStatus, date('Y-m-d H:i:s'), $userId]);

        $action = $newStatus === 'active' ? 'user_activated' : 'user_disabled';
        $this->audit($actorId, $action, $userId, ['new_status' => $newStatus]);

        return ['ok' => true, 'new_status' => $newStatus];
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
