<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;
use App\Services\User\CreateUserService;
use App\Services\User\UpdateUserService;
use App\Services\User\ToggleUserStatusService;
use App\Services\User\ResetPasswordService;
use App\Validators\CreateUserValidator;
use App\Validators\UpdateUserValidator;
use PDO;

final class UserController
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly CreateUserService $createService,
        private readonly UpdateUserService $updateService,
        private readonly ToggleUserStatusService $toggleService,
        private readonly ResetPasswordService $resetService,
        private readonly CreateUserValidator $createValidator,
        private readonly UpdateUserValidator $updateValidator,
        private readonly SessionStore $session
    ) {}

    /** GET /api/admin/users */
    public function index(HttpRequest $request): HttpResponse
    {
        $companyId = (int) ($this->session->get('company_id') ?? 0);

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, status, is_admin, created_at
             FROM users
             WHERE company_id = ? AND status != 'deleted'
             ORDER BY name ASC"
        );
        $stmt->execute([$companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['is_admin'] = (bool) $row['is_admin'];
        }
        unset($row);

        return HttpResponse::json($rows);
    }

    /** POST /api/admin/users */
    public function create(HttpRequest $request): HttpResponse
    {
        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $errors = $this->createValidator->validate($data);
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $actorId   = (int) ($this->session->get('user_id')    ?? 0);
        $email     = strtolower(trim((string) $data['email']));

        // Check email uniqueness within company
        $stmt = $this->pdo->prepare(
            "SELECT id FROM users WHERE company_id = ? AND email = ? AND status != 'deleted' LIMIT 1"
        );
        $stmt->execute([$companyId, $email]);
        if ($stmt->fetch()) {
            return $this->validationError(['email' => ['já está em uso nesta empresa']]);
        }

        $newId = $this->createService->execute(
            companyId:     $companyId,
            name:          trim((string) $data['name']),
            email:         $email,
            plainPassword: (string) $data['password'],
            isAdmin:       (bool) ($data['is_admin'] ?? false),
            actorId:       $actorId
        );

        return HttpResponse::json(['ok' => true, 'id' => $newId], 201);
    }

    /** PATCH /api/admin/users?id=X */
    public function update(HttpRequest $request): HttpResponse
    {
        $userId = (int) ($request->query()['id'] ?? 0);
        if ($userId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $errors = $this->updateValidator->validate($data);
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $actorId   = (int) ($this->session->get('user_id')    ?? 0);

        // Ensure user belongs to same company
        $stmt = $this->pdo->prepare(
            "SELECT id FROM users WHERE id = ? AND company_id = ? AND status != 'deleted' LIMIT 1"
        );
        $stmt->execute([$userId, $companyId]);
        if (!$stmt->fetch()) {
            return $this->apiError(404, 'not_found', 'Usuário não encontrado.', []);
        }

        // If changing email, check uniqueness
        if (!empty($data['email'])) {
            $email = strtolower(trim((string) $data['email']));
            $stmt2 = $this->pdo->prepare(
                "SELECT id FROM users WHERE company_id = ? AND email = ? AND id != ? AND status != 'deleted' LIMIT 1"
            );
            $stmt2->execute([$companyId, $email, $userId]);
            if ($stmt2->fetch()) {
                return $this->validationError(['email' => ['já está em uso nesta empresa']]);
            }
            $data['email'] = $email;
        }

        $ok = $this->updateService->execute($userId, $data, $actorId);

        return $ok
            ? HttpResponse::json(['ok' => true])
            : $this->apiError(500, 'internal_error', 'Falha ao atualizar usuário.', []);
    }

    /** POST /api/admin/users/toggle-status */
    public function toggleStatus(HttpRequest $request): HttpResponse
    {
        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $userId    = (int) ($data['user_id'] ?? 0);
        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $actorId   = (int) ($this->session->get('user_id')    ?? 0);

        if ($userId === 0) {
            return $this->validationError(['user_id' => ['obrigatório']]);
        }

        $result = $this->toggleService->execute($userId, $actorId, $companyId);

        if (!$result['ok']) {
            return $this->apiError(422, 'business_rule', $result['error'], []);
        }

        return HttpResponse::json(['ok' => true, 'new_status' => $result['new_status']]);
    }

    /** POST /api/admin/users/reset-password */
    public function resetPassword(HttpRequest $request): HttpResponse
    {
        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $userId      = (int)    ($data['user_id']      ?? 0);
        $newPassword = (string) ($data['new_password'] ?? '');
        $companyId   = (int)    ($this->session->get('company_id') ?? 0);
        $actorId     = (int)    ($this->session->get('user_id')    ?? 0);

        if ($userId === 0 || $newPassword === '') {
            return $this->validationError([
                'user_id'      => $userId === 0      ? ['obrigatório'] : [],
                'new_password' => $newPassword === '' ? ['obrigatório'] : [],
            ]);
        }

        $result = $this->resetService->execute($userId, $newPassword, $actorId, $companyId);

        if (!$result['ok']) {
            return $this->apiError(422, 'business_rule', $result['error'], []);
        }

        return HttpResponse::json(['ok' => true]);
    }

    /** DELETE /api/admin/users?id=X */
    public function delete(HttpRequest $request): HttpResponse
    {
        $userId    = (int) ($request->query()['id'] ?? 0);
        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $actorId   = (int) ($this->session->get('user_id')    ?? 0);

        if ($userId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        if ($userId === $actorId) {
            return $this->apiError(422, 'business_rule', 'Não é possível excluir o próprio usuário.', []);
        }

        // Fetch user
        $stmt = $this->pdo->prepare(
            "SELECT status, is_admin FROM users WHERE id = ? AND company_id = ? AND status != 'deleted' LIMIT 1"
        );
        $stmt->execute([$userId, $companyId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return $this->apiError(404, 'not_found', 'Usuário não encontrado.', []);
        }

        // Block if last active admin
        if ((bool) $user['is_admin'] && $user['status'] === 'active') {
            $countStmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM users WHERE company_id = ? AND is_admin = 1 AND status = 'active'"
            );
            $countStmt->execute([$companyId]);
            if ((int) $countStmt->fetchColumn() <= 1) {
                return $this->apiError(422, 'business_rule', 'Não é possível excluir o único administrador ativo da empresa.', []);
            }
        }

        // Block if owner of active projects
        $projStmt = $this->pdo->prepare(
            "SELECT p.id, p.name FROM project_members pm
             JOIN projects p ON p.id = pm.project_id
             WHERE pm.user_id = ? AND pm.role_in_project = 'owner'
             ORDER BY p.name ASC"
        );
        $projStmt->execute([$userId]);
        $projects = $projStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($projects)) {
            return $this->apiError(422, 'business_rule',
                'Usuário é owner de projetos ativos. Transfira a ownership antes de excluir.',
                ['projects' => $projects]
            );
        }

        // Soft-delete
        $this->pdo->prepare("UPDATE users SET status = 'deleted', updated_at = ? WHERE id = ?")
            ->execute([date('Y-m-d H:i:s'), $userId]);

        $this->pdo->prepare(
            'INSERT INTO admin_audit_log (actor_id, action, target_user_id, meta, created_at) VALUES (?, ?, ?, ?, ?)'
        )->execute([$actorId, 'user_deleted', $userId, json_encode([]), date('Y-m-d H:i:s')]);

        return HttpResponse::json(['ok' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function jsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function validationError(array $details): HttpResponse
    {
        return $this->apiError(422, 'validation_error', 'Payload inválido.', $details);
    }

    private function apiError(int $status, string $code, string $message, array $details): HttpResponse
    {
        return HttpResponse::json([
            'error' => ['code' => $code, 'message' => $message, 'details' => $details]
        ], $status);
    }
}
