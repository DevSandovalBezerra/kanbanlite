<?php

declare(strict_types=1);

namespace App\Services\ProjectMember;

use App\Repositories\PdoProjectMemberRepository;
use PDO;

final class AddMemberService
{
    public function __construct(
        private readonly PdoProjectMemberRepository $memberRepo,
        private readonly PDO $pdo
    ) {}

    /**
     * Adds a user to a project.
     *
     * Returns ['ok' => false, 'error' => '...'] on business-rule violation,
     * or ['ok' => true, 'id' => int] on success.
     */
    public function execute(
        int $projectId,
        int $userId,
        string $role,
        int $invitedBy,
        int $sessionCompanyId
    ): array {
        // S09: Only active users from the same company may be added
        $stmt = $this->pdo->prepare(
            "SELECT company_id FROM users WHERE id = ? AND status = 'active' LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['ok' => false, 'error' => 'Usuário não encontrado ou inativo.'];
        }

        if ((int) $row['company_id'] !== $sessionCompanyId) {
            return ['ok' => false, 'error' => 'Usuário pertence a outra empresa.'];
        }

        // Prevent duplicate membership
        if ($this->memberRepo->findMembership($projectId, $userId) !== null) {
            return ['ok' => false, 'error' => 'Usuário já é membro deste projeto.'];
        }

        $id = $this->memberRepo->add($projectId, $userId, $role, $invitedBy);

        return ['ok' => true, 'id' => $id];
    }
}
