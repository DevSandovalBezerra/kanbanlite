CREATE TABLE labels (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(60)     NOT NULL,
    color      VARCHAR(7)      NOT NULL DEFAULT '#6200EE',
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_company_label (company_id, name),
    KEY labels_company_id_idx (company_id),
    CONSTRAINT labels_company_id_fk FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_labels (
    task_id  BIGINT UNSIGNED NOT NULL,
    label_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (task_id, label_id),
    CONSTRAINT task_labels_task_fk  FOREIGN KEY (task_id)  REFERENCES tasks(id)  ON DELETE CASCADE,
    CONSTRAINT task_labels_label_fk FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
