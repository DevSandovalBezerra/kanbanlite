<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\ColumnDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\Column\CreateColumnService;
use App\Repositories\ColumnRepository;

final class ColumnController
{
    public function __construct(
        private readonly CreateColumnService $createColumnService,
        private readonly ColumnRepository $columnRepository
    ) {
    }

    public function create(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if (empty($payload['board_id']) || empty($payload['name'])) {
            return HttpResponse::json(['error' => 'board_id e name são obrigatórios'], 422);
        }

        $dto = new ColumnDTO(
            boardId: (int) $payload['board_id'],
            name: (string) $payload['name'],
            position: (int) ($payload['position'] ?? 1)
        );

        $id = $this->createColumnService->execute($dto);
        return HttpResponse::json(['id' => $id], 201);
    }

    public function reorder(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if (empty($payload['ordered_ids'])) {
            return HttpResponse::json(['error' => 'ordered_ids é obrigatório'], 422);
        }

        $result = $this->columnRepository->updatePositions($payload['ordered_ids']);
        return HttpResponse::json(['ok' => $result]);
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        return json_decode($raw ?? '', true);
    }
}
