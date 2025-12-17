-- SQL untuk membuat tabel kelas
-- Jalankan di phpMyAdmin

USE `e_admin_tu`;

CREATE TABLE IF NOT EXISTS `kelas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_kelas` VARCHAR(50) NOT NULL,
  `wali_kelas` VARCHAR(100),
  `tahun_ajaran` VARCHAR(20),
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `kelas` (`nama_kelas`, `tahun_ajaran`) VALUES
('X IPA 1', '2024/2025'),
('X IPA 2', '2024/2025'),
('X IPS 1', '2024/2025'),
('XI IPA 1', '2024/2025'),
('XII IPA 1', '2024/2025');

SELECT 'Tabel kelas berhasil dibuat!' AS message;
