-- Tabel Roles
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL UNIQUE,
  `permissions` TEXT NULL COMMENT 'JSON array of permissions',
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Roles
INSERT INTO `roles` (`role_name`, `permissions`, `description`) VALUES
('Super Admin', '["all"]', 'Akses penuh ke seluruh sistem'),
('Kepala Sekolah', '["dashboard","kepegawaian","keuangan","sarpras","kesiswaan","layanan_khusus"]', 'Akses monitoring dan approval'),
('Staff TU', '["dashboard","kepegawaian","persuratan","kesiswaan"]', 'Akses administrasi umum'),
('Bendahara', '["dashboard","keuangan"]', 'Akses manajemen keuangan'),
('Staff Sarpras', '["dashboard","sarpras"]', 'Akses manajemen aset dan inventaris');
