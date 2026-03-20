<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\AttachmentRepository;
use App\Services\Attachment\DeleteAttachmentService;
use App\Services\Attachment\UploadAttachmentService;
use App\Services\SessionStore;

final class AttachmentController
{
    public function __construct(
        private readonly AttachmentRepository $repository,
        private readonly UploadAttachmentService $uploadService,
        private readonly DeleteAttachmentService $deleteService,
        private readonly SessionStore $session,
    ) {
    }

    /** GET /api/attachments?task_id=X */
    public function index(HttpRequest $request): HttpResponse
    {
        $taskId = (int) ($request->query()['task_id'] ?? 0);

        if ($taskId === 0) {
            return $this->validationError(['task_id' => ['obrigatório']]);
        }

        $attachments = $this->repository->findByTaskId($taskId);

        return HttpResponse::json(array_map(fn ($a) => $a->toArray(), $attachments));
    }

    /** POST /api/attachments  (multipart/form-data, field "file", param "task_id") */
    public function upload(HttpRequest $request): HttpResponse
    {
        $userId = (int) $this->session->get('user_id');
        if ($userId === 0) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        $taskId = (int) ($request->query()['task_id'] ?? $_POST['task_id'] ?? 0);
        if ($taskId === 0) {
            return $this->validationError(['task_id' => ['obrigatório']]);
        }

        if (empty($_FILES['file'])) {
            return $this->validationError(['file' => ['nenhum arquivo enviado']]);
        }

        try {
            $id = $this->uploadService->execute($taskId, $userId, $_FILES['file']);

            $attachment = $this->repository->findById($id);

            return HttpResponse::json($attachment?->toArray() ?? ['id' => $id], 201);
        } catch (\RuntimeException $e) {
            return $this->apiError(422, 'upload_error', $e->getMessage(), []);
        }
    }

    /** DELETE /api/attachments?id=X */
    public function delete(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);

        if ($id === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $ok = $this->deleteService->execute($id);

        return $ok
            ? HttpResponse::json(['ok' => true])
            : $this->apiError(404, 'not_found', 'Anexo não encontrado.', []);
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
