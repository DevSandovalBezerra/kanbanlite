<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\ContactRepository;

final class ContactController
{
    public function __construct(private readonly ContactRepository $repository) {}

    public function index(HttpRequest $request): HttpResponse
    {
        $companyId = 1; // Mocked
        $contacts = $this->repository->findByCompanyId($companyId);
        
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseDir = str_replace('\\', '/', dirname($scriptName));
        if ($baseDir === '/' || $baseDir === '.') $baseDir = '';
        $appUrl = $scriptName;

        return \App\Helpers\View::render('pages.contacts', [
            'title' => 'Contatos da Equipe - KanbanLite',
            'contacts' => $contacts,
            'app_url' => $appUrl,
            'base_path' => $baseDir
        ]);
    }
}
