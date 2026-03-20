CREATE TABLE events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY events_company_id_idx (company_id),
  KEY events_project_id_idx (project_id),
  KEY events_created_by_idx (created_by),
  CONSTRAINT events_company_id_fk FOREIGN KEY (company_id) REFERENCES companies(id),
  CONSTRAINT events_project_id_fk FOREIGN KEY (project_id) REFERENCES projects(id),
  CONSTRAINT events_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
