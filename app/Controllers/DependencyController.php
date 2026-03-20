<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\Task\AddDependencyService;
use App\Services\Task\RemoveDependencyService;
use PDO;

final class DependencyController
{
    public function __construct(
        private readonly AddDependencyService $addService,
        private readonly RemoveDependencyService $removeService,
        private readonly PDO $pdo,
    ) {
    }

    /**
     * GET /api/dependencies?task_id=X
     * Returns tasks that block X and tasks that X blocks.
     */
    public function index(HttpRequest $request): HttpResponse
    {
        $taskId = (int) ($request->query()['task_id'] ?? 0);
        if ($taskId === 0) {
            return $this->validationError(['task_id' => ['obrigatório']]);
        }

        // Tasks that THIS task depends on (blockers)
        $stmt = $this->pdo->prepare(
            'SELECT t.id, t.title, t.status, t.priority
             FROM task_dependencies d
             JOIN tasks t ON t.id = d.depends_on_id
             WHERE d.task_id = ?'
        );
        $stmt->execute([$taskId]);
        $blockedBy = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tasks that depend on THIS task (blocked)
        $stmt2 = $this->pdo->prepare(
            'SELECT t.id, t.title, t.status, t.priority
             FROM task_dependencies d
             JOIN tasks t ON t.id = d.task_id
             WHERE d.depends_on_id = ?'
        );
        $stmt2->execute([$taskId]);
        $blocking = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return HttpResponse::json([
            'blocked_by' => $blockedBy,
            'blocking'   => $blocking,
        ]);
    }

    /** POST /api/dependencies  { task_id, depends_on_id } */
    public function add(HttpRequest $request): HttpResponse
    {
        $payload = $this->jsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $taskId      = (int) ($payload['task_id']      ?? 0);
        $dependsOnId = (int) ($payload['depends_on_id'] ?? 0);

        $errors = [];
        if ($taskId      === 0) $errors['task_id']      = ['obrigatório'];
        if ($dependsOnId === 0) $errors['depends_on_id'] = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        try {
            $ok = $this->addService->execute($taskId, $dependsOnId);
        } catch (\InvalidArgumentException $e) {
            return $this->apiError(422, 'invalid_dependency', $e->getMessage(), []);
        }

        return $ok
            ? HttpResponse::json(['ok' => true], 201)
            : $this->apiError(409, 'cycle_detected', 'Dependência criaria um ciclo ou já existe.', []);
    }

    /** DELETE /api/dependencies?task_id=X&depends_on_id=Y */
    public function remove(HttpRequest $request): HttpResponse
    {
        $taskId      = (int) ($request->query()['task_id']      ?? 0);
        $dependsOnId = (int) ($request->query()['depends_on_id'] ?? 0);

        $errors = [];
        if ($taskId      === 0) $errors['task_id']      = ['obrigatório'];
        if ($dependsOnId === 0) $errors['depends_on_id'] = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $this->removeService->execute($taskId, $dependsOnId);

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
        return $this->apiError(422, 'validation_error', 'Payload inválido.', $details);
    }

    private function apiError(int $status, string $code, string $message, array $details): HttpResponse
    {
        return HttpResponse::json([
            'error' => ['code' => $code, 'message' => $message, 'details' => $details],
        ], $status);
    }
}
