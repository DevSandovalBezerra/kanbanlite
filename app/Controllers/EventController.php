<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\EventDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\EventRepository;

final class EventController
{
    public function __construct(private readonly EventRepository $repository) {}

    public function index(HttpRequest $request): HttpResponse
    {
        $companyId = 1; // Mocked
        $events = $this->repository->findByCompanyId($companyId);
        
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseDir = str_replace('\\', '/', dirname($scriptName));
        if ($baseDir === '/' || $baseDir === '.') $baseDir = '';
        $appUrl = $scriptName;

        return \App\Helpers\View::render('pages.calendar', [
            'title' => 'Calendário da Equipe - KanbanLite',
            'events' => $events,
            'app_url' => $appUrl,
            'base_path' => $baseDir
        ]);
    }

    public function create(HttpRequest $request): HttpResponse
    {
        $data = $request->jsonBody();
        if (empty($data['title']) || empty($data['start_time']) || empty($data['end_time'])) {
            return HttpResponse::json(['ok' => false, 'error' => 'Título e horários são obrigatórios'], 400);
        }

        $companyId = 1; 
        $userId = 1;

        $dto = new EventDTO(
            id: null,
            companyId: $companyId,
            projectId: isset($data['project_id']) ? (int) $data['project_id'] : null,
            title: $data['title'],
            description: $data['description'] ?? '',
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            createdBy: $userId
        );

        try {
            $id = $this->repository->create($dto);
            return HttpResponse::json(['ok' => true, 'id' => $id], 201);
        } catch (\Exception $e) {
            return HttpResponse::json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
