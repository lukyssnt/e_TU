-- Tabel Activity Logs
DROP TABLE IF EXISTS `activity_logs`;

CREATE TABLE `activity_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `description` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
