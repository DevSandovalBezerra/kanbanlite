CREATE TABLE task_checklists (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    task_id    BIGINT UNSIGNED NOT NULL,
    title      VARCHAR(120)    NOT NULL DEFAULT 'Checklist',
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY task_checklists_task_id_idx (task_id),
    CONSTRAINT task_checklists_task_fk FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_checklist_items (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    checklist_id BIGINT UNSIGNED NOT NULL,
    body         VARCHAR(255)    NOT NULL,
    is_done      TINYINT(1)      NOT NULL DEFAULT 0,
    position     SMALLINT        NOT NULL DEFAULT 0,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY task_checklist_items_checklist_id_idx (checklist_id),
    CONSTRAINT task_checklist_items_checklist_fk FOREIGN KEY (checklist_id) REFERENCES task_checklists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
