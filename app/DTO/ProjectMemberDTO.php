<?php

declare(strict_types=1);

namespace App\DTO;

final class ProjectMemberDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $projectId,
        public readonly int $userId,
        public readonly string $roleInProject,
        public readonly ?int $invitedBy,
        public readonly ?string $acceptedAt,
        public readonly ?string $userName = null,
        public readonly ?string $userEmail = null,
        public readonly ?string $userStatus = null,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:            isset($row['id'])       ? (int) $row['id']       : null,
            projectId:     (int) $row['project_id'],
            userId:        (int) $row['user_id'],
            roleInProject: (string) $row['role_in_project'],
            invitedBy:     isset($row['invited_by'])  ? (int) $row['invited_by']  : null,
            acceptedAt:    $row['accepted_at'] ?? null,
            userName:      $row['user_name']   ?? null,
            userEmail:     $row['user_email']  ?? null,
            userStatus:    $row['user_status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'project_id'      => $this->projectId,
            'user_id'         => $this->userId,
            'role_in_project' => $this->roleInProject,
            'invited_by'      => $this->invitedBy,
            'accepted_at'     => $this->acceptedAt,
            'user_name'       => $this->userName,
            'user_email'      => $this->userEmail,
            'user_status'     => $this->userStatus,
        ];
    }
}
