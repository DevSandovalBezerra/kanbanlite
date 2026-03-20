<?php

declare(strict_types=1);

namespace App\Services\Attachment;

use App\DTO\AttachmentDTO;
use App\Repositories\AttachmentRepository;

final class UploadAttachmentService
{
    private const MAX_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB

    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function __construct(
        private readonly AttachmentRepository $repository,
        private readonly string $uploadBasePath,
    ) {
    }

    /**
     * @param  array $file  Entry from $_FILES['file']
     * @throws \RuntimeException on validation or IO failure
     */
    public function execute(int $taskId, int $userId, array $file): int
    {
        $this->validate($file);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uuid     = $this->uuid4();
        $relative = "tasks/{$taskId}/{$uuid}.{$ext}";
        $absolute = rtrim($this->uploadBasePath, '/') . '/' . $relative;

        $dir = dirname($absolute);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException("Não foi possível criar o diretório de upload.");
        }

        if (!move_uploaded_file($file['tmp_name'], $absolute)) {
            throw new \RuntimeException("Falha ao mover o arquivo enviado.");
        }

        $dto = new AttachmentDTO(
            taskId: $taskId,
            uploadedBy: $userId,
            filename: $file['name'],
            filepath: $relative,
            mimeType: $file['type'],
            sizeBytes: (int) $file['size'],
        );

        return $this->repository->create($dto);
    }

    private function validate(array $file): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException("Erro no upload (código {$file['error']}).");
        }

        if ((int) $file['size'] > self::MAX_SIZE_BYTES) {
            throw new \RuntimeException("Arquivo excede o limite de 10 MB.");
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($file['tmp_name']);

        if (!in_array($mimeReal, self::ALLOWED_MIME, true)) {
            throw new \RuntimeException("Tipo de arquivo não permitido: {$mimeReal}.");
        }

        // Sync the reported mime_type with the real one (防 spoofing)
        $file['type'] = $mimeReal;
    }

    private function uuid4(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
