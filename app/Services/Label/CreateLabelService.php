<?php

declare(strict_types=1);

namespace App\Services\Label;

use App\DTO\LabelDTO;
use App\Repositories\LabelRepository;

final class CreateLabelService
{
    private const VALID_HEX = '/^#[0-9A-Fa-f]{6}$/';

    public function __construct(private readonly LabelRepository $repository)
    {
    }

    public function execute(int $companyId, string $name, string $color): int
    {
        $name  = trim($name);
        $color = strtoupper(trim($color));

        if (!preg_match(self::VALID_HEX, $color)) {
            $color = '#6200EE';
        }

        $dto = new LabelDTO(
            companyId: $companyId,
            name: $name,
            color: $color,
        );

        return $this->repository->create($dto);
    }
}
