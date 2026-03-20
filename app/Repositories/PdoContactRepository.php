<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ContactDTO;
use PDO;

final class PdoContactRepository implements ContactRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare("SELECT id, name, email, status FROM users WHERE company_id = :company_id AND status = 'active' ORDER BY name ASC");
        $stmt->execute(['company_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function(array $row) {
            return new ContactDTO(
                (int) $row['id'],
                (string) $row['name'],
                (string) $row['email'],
                (string) $row['status'],
                "https://api.dicebear.com/7.x/avataaars/svg?seed=" . urlencode($row['name'])
            );
        }, $rows);
    }
}
