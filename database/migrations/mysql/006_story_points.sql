ALTER TABLE tasks
    ADD COLUMN story_points TINYINT UNSIGNED NULL DEFAULT NULL
        COMMENT 'Estimativa Fibonacci: 1, 2, 3, 5, 8, 13, 21'
        AFTER priority;
