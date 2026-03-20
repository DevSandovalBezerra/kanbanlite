<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\LabelRepository;
use App\Services\Label\AttachLabelService;
use App\Services\Label\CreateLabelService;
use App\Services\Label\DetachLabelService;
use App\Services\SessionStore;

final class LabelController
{
    public function __construct(
        private readonly LabelRepository $labelRepo,
        private readonly CreateLabelService $createService,
        private readonly AttachLabelService $attachService,
        private readonly DetachLabelService $detachService,
        private readonly SessionStore $session,
    ) {
    }

    /** GET /api/labels?company_id=X  — lista labels da empresa */
    public function index(HttpRequest $request): HttpResponse
    {
        $companyId = (int) ($request->query()['company_id'] ?? 0);

        if ($companyId === 0) {
            return $this->validationError(['company_id' => ['obrigatório']]);
        }

        $labels = $this->labelRepo->findByCompanyId($companyId);

        return HttpResponse::json(array_map(fn ($l) => $l->toArray(), $labels));
    }

    /** POST /api/labels  — cria label */
    public function create(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $companyId = (int) ($payload['company_id'] ?? 0);
        $name      = trim((string) ($payload['name'] ?? ''));
        $color     = (string) ($payload['color'] ?? '#6200EE');

        $errors = [];
        if ($companyId === 0) $errors['company_id'] = ['obrigatório'];
        if ($name === '')     $errors['name']       = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $id    = $this->createService->execute($companyId, $name, $color);
        $label = $this->labelRepo->findById($id);

        return HttpResponse::json($label?->toArray() ?? ['id' => $id], 201);
    }

    /** DELETE /api/labels?id=X  — remove label */
    public function delete(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);

        if ($id === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $ok = $this->labelRepo->delete($id);

        return $ok
            ? HttpResponse::json(['ok' => true])
            : $this->apiError(404, 'not_found', 'Label não encontrada.', []);
    }

    /** POST /api/tasks/{task_id}/labels  — attach label ao card */
    public function attach(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $taskId  = (int) ($payload['task_id']  ?? 0);
        $labelId = (int) ($payload['label_id'] ?? 0);

        $errors = [];
        if ($taskId  === 0) $errors['task_id']  = ['obrigatório'];
        if ($labelId === 0) $errors['label_id'] = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $ok = $this->attachService->execute($taskId, $labelId);

        return $ok
            ? HttpResponse::json(['ok' => true])
            : $this->apiError(404, 'not_found', 'Label não encontrada.', []);
    }

    /** DELETE /api/task-labels?task_id=X&label_id=Y  — detach label do card */
    public function detach(HttpRequest $request): HttpResponse
    {
        $taskId  = (int) ($request->query()['task_id']  ?? 0);
        $labelId = (int) ($request->query()['label_id'] ?? 0);

        $errors = [];
        if ($taskId  === 0) $errors['task_id']  = ['obrigatório'];
        if ($labelId === 0) $errors['label_id'] = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $this->detachService->execute($taskId, $labelId);

        return HttpResponse::json(['ok' => true]);
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function validationError(array $details): HttpResponse
    {
        return $this->apiError(422, 'validation_error', 'Payload inválido.', $details);
    }

    private function apiError(int $statusCode, string $code, string $message, array $details): HttpResponse
    {
        return HttpResponse::json([
            'error' => [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $statusCode);
    }
}
