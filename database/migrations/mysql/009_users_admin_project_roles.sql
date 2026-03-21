-- Migration 009: Admin flag, project member invite columns, and audit log

-- Add admin flag to users
ALTER TABLE users
  ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

-- Restructure project_members: replace composite PK with surrogate id PK
ALTER TABLE project_members DROP FOREIGN KEY project_members_project_id_fk;
ALTER TABLE project_members DROP FOREIGN KEY project_members_user_id_fk;
ALTER TABLE project_members DROP PRIMARY KEY;
ALTER TABLE project_members ADD COLUMN id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE project_members ADD CONSTRAINT pm_project_fk FOREIGN KEY (project_id) REFERENCES projects(id);
ALTER TABLE project_members ADD CONSTRAINT pm_user_fk FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE project_members ADD UNIQUE KEY uq_project_member (project_id, user_id);

-- Add invite tracking columns to project_members
ALTER TABLE project_members
  ADD COLUMN invited_by  BIGINT UNSIGNED NULL AFTER role_in_project,
  ADD COLUMN accepted_at DATETIME        NULL AFTER invited_by,
  ADD COLUMN updated_at  DATETIME        NULL,
  ADD CONSTRAINT project_members_invited_by_fk
    FOREIGN KEY (invited_by) REFERENCES users(id);

-- Audit trail for administrative actions
CREATE TABLE admin_audit_log (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id       BIGINT UNSIGNED NOT NULL COMMENT 'Who performed the action',
    action         VARCHAR(50)     NOT NULL COMMENT 'E.g.: user_created, user_disabled, role_changed',
    target_user_id BIGINT UNSIGNED NULL     COMMENT 'Affected user, if applicable',
    meta           JSON            NULL     COMMENT 'Before/after snapshot of the change',
    created_at     DATETIME        NOT NULL,
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
