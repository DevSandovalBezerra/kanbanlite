CREATE TABLE messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  is_read BOOLEAN NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY messages_sender_id_idx (sender_id),
  KEY messages_receiver_id_idx (receiver_id),
  CONSTRAINT messages_sender_id_fk FOREIGN KEY (sender_id) REFERENCES users(id),
  CONSTRAINT messages_receiver_id_fk FOREIGN KEY (receiver_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
