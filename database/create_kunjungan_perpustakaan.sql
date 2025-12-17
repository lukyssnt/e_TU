CREATE TABLE IF NOT EXISTS `kunjungan_perpustakaan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `siswa_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `waktu` TIME NOT NULL,
  `keperluan` VARCHAR(255) DEFAULT 'Membaca',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
