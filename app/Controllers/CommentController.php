<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\CommentRepository;
use App\Services\Comment\CreateCommentService;
use App\Services\Comment\DeleteCommentService;
use App\Services\SessionStore;

final class CommentController
{
    public function __construct(
        private readonly CommentRepository $commentRepo,
        private readonly CreateCommentService $createService,
        private readonly DeleteCommentService $deleteService,
        private readonly SessionStore $session,
    ) {
    }

    public function index(HttpRequest $request): HttpResponse
    {
        $taskId = (int) ($request->query()['task_id'] ?? 0);

        if ($taskId === 0) {
            return $this->validationError(['task_id' => ['obrigatório']]);
        }

        $comments = $this->commentRepo->findByTaskId($taskId);

        return HttpResponse::json(array_map(
            fn ($c) => $c->toArray(),
            $comments
        ));
    }

    public function create(HttpRequest $request): HttpResponse
    {
        $userId = $this->session->get('user_id');
        if ($userId === null) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $taskId = (int) ($payload['task_id'] ?? 0);
        $body   = trim((string) ($payload['body'] ?? ''));

        $errors = [];
        if ($taskId === 0)    $errors['task_id'] = ['obrigatório'];
        if ($body === '')     $errors['body']    = ['obrigatório'];

        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $id = $this->createService->execute($taskId, (int) $userId, $body);

        $comment = $this->commentRepo->findById($id);

        return HttpResponse::json($comment?->toArray() ?? ['id' => $id], 201);
    }

    public function delete(HttpRequest $request): HttpResponse
    {
        $userId = $this->session->get('user_id');
        if ($userId === null) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        $commentId = (int) ($request->query()['id'] ?? 0);
        if ($commentId === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $ok = $this->deleteService->execute($commentId, (int) $userId);

        if (!$ok) {
            return $this->apiError(403, 'forbidden', 'Comentário não encontrado ou sem permissão.', []);
        }

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
