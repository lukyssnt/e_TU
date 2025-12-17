-- Tabel Agenda Kegiatan
DROP TABLE IF EXISTS `agenda`;

CREATE TABLE `agenda` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE,
  `waktu_mulai` TIME,
  `waktu_selesai` TIME,
  `lokasi` VARCHAR(255),
  `penanggungjawab` VARCHAR(100),
  `status` ENUM('Akan Datang', 'Berlangsung', 'Selesai') DEFAULT 'Akan Datang',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
