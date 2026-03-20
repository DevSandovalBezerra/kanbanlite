CREATE TABLE task_dependencies (
    task_id       BIGINT UNSIGNED NOT NULL,
    depends_on_id BIGINT UNSIGNED NOT NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (task_id, depends_on_id),
    CONSTRAINT task_dep_task_fk    FOREIGN KEY (task_id)       REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT task_dep_blocker_fk FOREIGN KEY (depends_on_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT task_dep_no_self    CHECK (task_id != depends_on_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
