-- Tabel Cuti Pegawai
CREATE TABLE IF NOT EXISTS `cuti` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT NOT NULL,
  `jenis_cuti` VARCHAR(50) NOT NULL COMMENT 'Tahunan, Sakit, Melahirkan, Besar, Alasan Penting, Lainnya',
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `jumlah_hari` INT NOT NULL,
  `keterangan` TEXT,
  `status` ENUM('Menunggu', 'Disetujui', 'Ditolak') DEFAULT 'Menunggu',
  `disetujui_oleh` INT NULL, -- ID User yang menyetujui (Kepala Sekolah/TU)
  `tanggal_persetujuan` DATETIME NULL,
  `alasan_penolakan` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index
CREATE INDEX idx_pegawai_cuti ON cuti(pegawai_id);
CREATE INDEX idx_status_cuti ON cuti(status);
