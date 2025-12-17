-- SQL untuk membuat tabel mutasi_siswa
-- Jalankan di phpMyAdmin

USE `e_admin_tu`;

-- Tabel Mutasi Siswa
CREATE TABLE IF NOT EXISTS `mutasi_siswa` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `siswa_id` INT NOT NULL,
  `jenis_mutasi` ENUM('Masuk','Keluar') NOT NULL,
  `tanggal_mutasi` DATE NOT NULL,
  `alasan` TEXT NOT NULL,
  `sekolah_asal` VARCHAR(100) COMMENT 'Untuk mutasi masuk',
  `sekolah_tujuan` VARCHAR(100) COMMENT 'Untuk mutasi keluar',
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Tabel mutasi_siswa berhasil dibuat!' AS message;
