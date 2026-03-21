<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Policies\ProjectPolicy;
use App\Repositories\PdoProjectMemberRepository;
use App\Repositories\PdoProjectRepository;
use App\Services\ProjectMember\AddMemberService;
use App\Services\ProjectMember\RemoveMemberService;
use App\Services\ProjectMember\UpdateMemberRoleService;
use App\Services\SessionStore;
use App\Validators\InviteMemberValidator;

final class ProjectMemberController
{
    public function __construct(
        private readonly PdoProjectMemberRepository $memberRepo,
        private readonly PdoProjectRepository $projectRepo,
        private readonly AddMemberService $addService,
        private readonly UpdateMemberRoleService $updateRoleService,
        private readonly RemoveMemberService $removeService,
        private readonly InviteMemberValidator $validator,
        private readonly ProjectPolicy $policy,
        private readonly SessionStore $session
    ) {}

    /** GET /api/project-members?project_id=X */
    public function index(HttpRequest $request): HttpResponse
    {
        $projectId = (int) ($request->query()['project_id'] ?? 0);
        if ($projectId === 0) {
            return $this->validationError(['project_id' => ['obrigatório']]);
        }

        // Tenant check
        $companyId = (int) ($this->session->get('company_id') ?? 0);
        if (!$this->projectRepo->belongsToCompany($projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Projeto não encontrado.', []);
        }

        // Must be a member to list
        if (!$this->policy->canView($projectId)) {
            return $this->apiError(403, 'forbidden', 'Sem acesso a este projeto.', []);
        }

        $members = $this->memberRepo->findByProjectId($projectId);

        return HttpResponse::json(array_map(fn ($m) => $m->toArray(), $members));
    }

    /** POST /api/project-members */
    public function add(HttpRequest $request): HttpResponse
    {
        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $projectId = (int) $data['project_id'];
        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $actorId   = (int) ($this->session->get('user_id')    ?? 0);

        // Tenant check
        if (!$this->projectRepo->belongsToCompany($projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Projeto não encontrado.', []);
        }

        // Permission check: manager or owner
        if (!$this->policy->canInvite($projectId)) {
            return $this->apiError(403, 'forbidden', 'Apenas manager ou owner podem adicionar membros.', []);
        }

        $result = $this->addService->execute(
            projectId:       $projectId,
            userId:          (int) $data['user_id'],
            role:            (string) $data['role_in_project'],
            invitedBy:       $actorId,
            sessionCompanyId: $companyId
        );

        if (!$result['ok']) {
            return $this->apiError(422, 'business_rule', $result['error'], []);
        }

        return HttpResponse::json(['ok' => true, 'id' => $result['id']], 201);
    }

    /** PATCH /api/project-members?id=X */
    public function updateRole(HttpRequest $request): HttpResponse
    {
        $membershipId = (int) ($request->query()['id'] ?? 0);
        if ($membershipId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $newRole = (string) ($data['role_in_project'] ?? '');
        if (!in_array($newRole, ['owner', 'manager', 'editor', 'viewer'], true)) {
            return $this->validationError(['role_in_project' => ['deve ser owner, manager, editor ou viewer']]);
        }

        // Find membership and validate tenant
        $member    = $this->memberRepo->findById($membershipId);
        $companyId = (int) ($this->session->get('company_id') ?? 0);

        if (!$member || !$this->projectRepo->belongsToCompany($member->projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Membro não encontrado.', []);
        }

        // Only owner can alter roles (S12)
        if (!$this->policy->canAlterRoles($member->projectId)) {
            return $this->apiError(403, 'forbidden', 'Apenas o owner pode alterar papéis de membros.', []);
        }

        $actorId = (int) ($this->session->get('user_id') ?? 0);
        $result  = $this->updateRoleService->execute($membershipId, $newRole, $actorId);

        if (!$result['ok']) {
            return $this->apiError(422, 'business_rule', $result['error'], []);
        }

        return HttpResponse::json(['ok' => true]);
    }

    /** DELETE /api/project-members?id=X */
    public function remove(HttpRequest $request): HttpResponse
    {
        $membershipId = (int) ($request->query()['id'] ?? 0);
        if ($membershipId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $member    = $this->memberRepo->findById($membershipId);
        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $actorId   = (int) ($this->session->get('user_id') ?? 0);

        // Tenant check
        if (!$member || !$this->projectRepo->belongsToCompany($member->projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Membro não encontrado.', []);
        }

        // Owner can remove anyone; member can remove themselves
        $isOwner      = $this->policy->canManageProject($member->projectId);
        $isSelf       = $member->userId === $actorId;

        if (!$isOwner && !$isSelf) {
            return $this->apiError(403, 'forbidden', 'Sem permissão para remover este membro.', []);
        }

        $result = $this->removeService->execute($membershipId, $actorId);

        if (!$result['ok']) {
            return $this->apiError(422, 'business_rule', $result['error'], []);
        }

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
