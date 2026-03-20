<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\TaskDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;
use App\Services\Task\CreateTaskService;
use App\Services\Task\MoveTaskService;

final class TaskController
{
    public function __construct(
        private readonly CreateTaskService $createTaskService,
        private readonly MoveTaskService $moveTaskService,
        private readonly \App\Repositories\TaskRepository $taskRepo,
        private readonly SessionStore $session
    ) {
    }

    public function findTasksByColumnId(HttpRequest $request): HttpResponse
    {
        $columnId = (int) ($request->query()['column_id'] ?? 0);
        if ($columnId === 0) {
            return $this->validationError(['column_id' => ['obrigatório']]);
        }

        $tasks = $this->taskRepo->findByColumnId($columnId);

        return HttpResponse::json(array_map(fn ($t) => $t->toArray(), $tasks));
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

        // Basic validation
        $errors = [];
        if (empty($payload['title'])) $errors['title'] = ['obrigatório'];
        if (empty($payload['column_id'])) $errors['column_id'] = ['obrigatório'];
        
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $dto = new TaskDTO(
            columnId: (int) $payload['column_id'],
            title: (string) $payload['title'],
            description: self::sanitizeHtml((string) ($payload['description'] ?? '')),
            priority: (string) ($payload['priority'] ?? 'medium'),
            status: 'active',
            position: (int) ($payload['position'] ?? 1),
            createdBy: (int) $userId,
            assignedTo: isset($payload['assigned_to']) ? (int) $payload['assigned_to'] : null,
            deadline: isset($payload['deadline']) ? new \DateTimeImmutable($payload['deadline']) : null,
            storyPoints: isset($payload['story_points']) ? (int) $payload['story_points'] : null,
        );

        $id = $this->createTaskService->execute($dto);

        return HttpResponse::json(['id' => $id], 201);
    }

    public function update(HttpRequest $request): HttpResponse
    {
        $taskId = (int) ($request->query()['id'] ?? 0);
        if ($taskId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $existing = $this->taskRepo->findById($taskId);
        if ($existing === null) {
            return $this->apiError(404, 'not_found', 'Tarefa não encontrada.', []);
        }

        $updated = new TaskDTO(
            id: $existing->id,
            columnId: $existing->columnId,
            title: isset($payload['title']) ? (string) $payload['title'] : $existing->title,
            description: isset($payload['description'])
                ? self::sanitizeHtml((string) $payload['description'])
                : $existing->description,
            priority: isset($payload['priority']) ? (string) $payload['priority'] : $existing->priority,
            status: $existing->status,
            position: $existing->position,
            createdBy: $existing->createdBy,
            assignedTo: array_key_exists('assigned_to', $payload)
                ? ($payload['assigned_to'] !== null ? (int) $payload['assigned_to'] : null)
                : $existing->assignedTo,
            deadline: array_key_exists('deadline', $payload)
                ? ($payload['deadline'] ? new \DateTimeImmutable($payload['deadline']) : null)
                : $existing->deadline,
            storyPoints: array_key_exists('story_points', $payload)
                ? ($payload['story_points'] !== null ? (int) $payload['story_points'] : null)
                : $existing->storyPoints,
        );

        $ok = $this->taskRepo->update($updated);

        return $ok
            ? HttpResponse::json(['id' => $taskId, 'ok' => true])
            : $this->apiError(500, 'internal_error', 'Falha ao atualizar tarefa.', []);
    }

    public function move(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $taskId = (int) ($payload['id'] ?? 0);
        $toColumnId = (int) ($payload['to_column_id'] ?? 0);
        $toPosition = (int) ($payload['to_position'] ?? 1);

        if ($taskId === 0 || $toColumnId === 0) {
            return $this->validationError(['id' => ['obrigatório'], 'to_column_id' => ['obrigatório']]);
        }

        $userId = (int) $this->session->get('user_id');
        $result = $this->moveTaskService->execute($taskId, $toColumnId, $toPosition, $userId);

        if ($result) {
            return HttpResponse::json(['ok' => true]);
        }

        return $this->apiError(500, 'internal_error', 'Falha ao mover tarefa.', []);
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
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

    private static function sanitizeHtml(string $html): string
    {
        return strip_tags(
            $html,
            '<p><br><strong><em><u><s><ul><ol><li><h2><h3><a><img><blockquote><pre><code><span>'
        );
    }
}
