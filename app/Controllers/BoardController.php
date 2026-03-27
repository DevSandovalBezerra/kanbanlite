<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\BoardDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Policies\ProjectPolicy;
use App\Repositories\BoardRepository;
use App\Repositories\PdoProjectRepository;
use App\Services\Board\CreateBoardService;
use App\Services\SessionStore;

final class BoardController
{
    public function __construct(
        private readonly CreateBoardService $createBoardService,
        private readonly BoardRepository $boardRepository,
        private readonly SessionStore $session,
        private readonly ?PdoProjectRepository $projectRepo = null,
        private readonly ?ProjectPolicy $policy = null
    ) {
    }

    public function create(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $userId = $this->session->get('user_id');
        if ($userId === null) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        if (empty($payload['name']) || empty($payload['project_id'])) {
            return $this->validationError(['name' => ['obrigatório'], 'project_id' => ['obrigatório']]);
        }

        $projectId = (int) $payload['project_id'];

        // Double guard (S08)
        if ($this->projectRepo !== null && $this->policy !== null) {
            $companyId = (int) ($this->session->get('company_id') ?? 0);

            // Step 1: tenant isolation
            if (!$this->projectRepo->belongsToCompany($projectId, $companyId)) {
                return $this->apiError(404, 'not_found', 'Projeto não encontrado.', []);
            }

            // Step 2: role check
            if (!$this->policy->canManageBoard($projectId)) {
                return $this->apiError(403, 'forbidden', 'Requer papel manager ou superior.', []);
            }
        }

        $dto = new BoardDTO(
            projectId: $projectId,
            name: (string) $payload['name'],
            createdBy: (int) $userId
        );

        $id = $this->createBoardService->execute($dto);

        return HttpResponse::json(['id' => $id], 201);
    }

    public function show(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);
        if ($id === 0) {
            return $this->apiError(400, 'bad_request', 'ID ausente.', []);
        }

        $board = $this->boardRepository->findById($id);
        if ($board === null) {
            return $this->apiError(404, 'not_found', 'Board não encontrado.', []);
        }

        if ($this->projectRepo !== null && $this->policy !== null) {
            $companyId = (int) ($this->session->get('company_id') ?? 0);
            if (!$this->projectRepo->belongsToCompany($board->projectId, $companyId)) {
                return $this->apiError(404, 'not_found', 'Board não encontrado.', []);
            }

            if (!$this->policy->canView($board->projectId)) {
                return $this->apiError(403, 'forbidden', 'Sem acesso a este projeto.', []);
            }
        }

        return HttpResponse::json($board->toArray());
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        return json_decode($raw, true);
    }

    private function validationError(array $details): HttpResponse
    {
        return $this->apiError(422, 'validation_error', 'Payload inválido.', $details);
    }

    private function apiError(int $statusCode, string $code, string $message, array $details): HttpResponse
    {
        return HttpResponse::json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ], $statusCode);
    }
}
