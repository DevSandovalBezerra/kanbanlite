CREATE TABLE task_attachments (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    task_id     BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    filename    VARCHAR(255)    NOT NULL,
    filepath    VARCHAR(500)    NOT NULL,
    mime_type   VARCHAR(100)    NOT NULL,
    size_bytes  INT UNSIGNED    NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY task_attachments_task_id_idx (task_id),
    CONSTRAINT task_attachments_task_fk FOREIGN KEY (task_id)     REFERENCES tasks(id)  ON DELETE CASCADE,
    CONSTRAINT task_attachments_user_fk FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
