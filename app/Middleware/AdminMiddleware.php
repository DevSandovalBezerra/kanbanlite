<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;
use PDO;

/**
 * Enforces that the authenticated user is an admin.
 * ALWAYS revalidates against the database — never trusts the session cache.
 */
final class AdminMiddleware
{
    public function __construct(
        private readonly SessionStore $session,
        private readonly PDO $pdo
    ) {}

    public function __invoke(HttpRequest $request, callable $next): HttpResponse
    {
        $userId = (int) ($this->session->get('user_id') ?? 0);
        if ($userId === 0) {
            return $this->forbidden();
        }

        // Revalidate against database — never trust session cache for authorization
        $stmt = $this->pdo->prepare(
            "SELECT is_admin FROM users WHERE id = ? AND status = 'active' LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !(bool) $row['is_admin']) {
            return $this->forbidden();
        }

        return $next($request);
    }

    private function forbidden(): HttpResponse
    {
        return HttpResponse::json([
            'error' => [
                'code'    => 'forbidden',
                'message' => 'Acesso negado. Requer privilégios de administrador.',
                'details' => [],
            ]
        ], 403);
    }
}
