-- Tabel Settings
DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT NULL,
  `description` VARCHAR(255) NULL,
  `type` VARCHAR(20) DEFAULT 'text',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`, `type`) VALUES
('app_name', 'E-ADMIN TU MA AL IHSAN', 'Nama Aplikasi', 'text'),
('school_name', 'MA AL IHSAN', 'Nama Sekolah', 'text'),
('school_address', 'Jl. Raya Cicalengka - Majalaya Km. 05', 'Alamat Sekolah', 'text'),
('school_phone', '(022) 1234567', 'Nomor Telepon', 'text'),
('school_email', 'info@maalihsan.sch.id', 'Email Sekolah', 'text'),
('app_logo', 'assets/img/logo.png', 'Logo Aplikasi', 'image'),
('maintenance_mode', '0', 'Mode Maintenance', 'boolean');
