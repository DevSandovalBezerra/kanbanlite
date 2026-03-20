<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\ChecklistRepository;
use App\Services\Checklist\AddChecklistItemService;
use App\Services\Checklist\CreateChecklistService;
use App\Services\Checklist\DeleteChecklistItemService;
use App\Services\Checklist\ToggleChecklistItemService;

final class ChecklistController
{
    public function __construct(
        private readonly ChecklistRepository $repository,
        private readonly CreateChecklistService $createService,
        private readonly AddChecklistItemService $addItemService,
        private readonly ToggleChecklistItemService $toggleService,
        private readonly DeleteChecklistItemService $deleteItemService,
    ) {
    }

    /** GET /api/checklists?task_id=X */
    public function index(HttpRequest $request): HttpResponse
    {
        $taskId = (int) ($request->query()['task_id'] ?? 0);

        if ($taskId === 0) {
            return $this->validationError(['task_id' => ['obrigatório']]);
        }

        $checklists = $this->repository->findByTaskId($taskId);

        return HttpResponse::json(array_map(fn ($c) => $c->toArray(), $checklists));
    }

    /** POST /api/checklists — cria checklist */
    public function create(HttpRequest $request): HttpResponse
    {
        $payload = $this->jsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $taskId = (int) ($payload['task_id'] ?? 0);
        $title  = (string) ($payload['title'] ?? 'Checklist');

        if ($taskId === 0) {
            return $this->validationError(['task_id' => ['obrigatório']]);
        }

        $id = $this->createService->execute($taskId, $title);

        return HttpResponse::json(['id' => $id], 201);
    }

    /** DELETE /api/checklists?id=X — remove checklist inteiro */
    public function delete(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);

        if ($id === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $this->repository->deleteChecklist($id);

        return HttpResponse::json(['ok' => true]);
    }

    /** POST /api/checklist-items — adiciona item */
    public function addItem(HttpRequest $request): HttpResponse
    {
        $payload = $this->jsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $checklistId = (int) ($payload['checklist_id'] ?? 0);
        $body        = trim((string) ($payload['body'] ?? ''));
        $position    = (int) ($payload['position'] ?? 0);

        $errors = [];
        if ($checklistId === 0) $errors['checklist_id'] = ['obrigatório'];
        if ($body === '')       $errors['body']         = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $id = $this->addItemService->execute($checklistId, $body, $position);

        return HttpResponse::json(['id' => $id], 201);
    }

    /** PATCH /api/checklist-items — toggle is_done */
    public function toggleItem(HttpRequest $request): HttpResponse
    {
        $payload = $this->jsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $itemId = (int) ($payload['id'] ?? 0);
        $isDone = (bool) ($payload['is_done'] ?? false);

        if ($itemId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $this->toggleService->execute($itemId, $isDone);

        return HttpResponse::json(['ok' => true]);
    }

    /** DELETE /api/checklist-items?id=X — remove item */
    public function deleteItem(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);

        if ($id === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $this->deleteItemService->execute($id);

        return HttpResponse::json(['ok' => true]);
    }

    private function jsonBody(HttpRequest $request): ?array
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
        return HttpResponse::json([
            'error' => ['code' => 'validation_error', 'message' => 'Payload inválido.', 'details' => $details],
        ], 422);
    }
}
