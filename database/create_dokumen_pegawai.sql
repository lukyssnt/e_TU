-- Tabel Dokumen Pegawai
CREATE TABLE IF NOT EXISTS `dokumen_pegawai` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT NOT NULL,
  `jenis_dokumen` VARCHAR(50) NOT NULL COMMENT 'ktp, kk, npwp, sk_pengangkatan, ijazah_terakhir, transkrip_nilai, sertifikat_pelatihan, foto, lainnya',
  `nama_file` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_doc_pegawai` (`pegawai_id`, `jenis_dokumen`),
  FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk performa
CREATE INDEX idx_pegawai_dokumen ON dokumen_pegawai(pegawai_id);
CREATE INDEX idx_jenis_dokumen_pegawai ON dokumen_pegawai(jenis_dokumen);
