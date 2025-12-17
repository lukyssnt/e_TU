-- SQL untuk membuat tabel dokumen_siswa
-- Jalankan di phpMyAdmin

USE `e_admin_tu`;

-- Tabel Dokumen Siswa
CREATE TABLE IF NOT EXISTS `dokumen_siswa` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `siswa_id` INT NOT NULL,
  `jenis_dokumen` VARCHAR(50) NOT NULL COMMENT 'kk, akte, ijazah_sd, ijazah_smp, ktp_ayah, ktp_ibu, skl, kip, kis, pkh, foto, lainnya',
  `nama_file` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_doc` (`siswa_id`, `jenis_dokumen`),
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk performa
CREATE INDEX idx_siswa_dokumen ON dokumen_siswa(siswa_id);
CREATE INDEX idx_jenis_dokumen ON dokumen_siswa(jenis_dokumen);

SELECT 'Tabel dokumen_siswa berhasil dibuat!' AS message;

-- Daftar jenis dokumen yang didukung:
-- kk          = Kartu Keluarga
-- akte        = Akte Kelahiran
-- ijazah_sd   = Ijazah SD/MI
-- ijazah_smp  = Ijazah SMP/MTs
-- ktp_ayah    = KTP Ayah
-- ktp_ibu     = KTP Ibu
-- skl         = Surat Keterangan Lulus
-- kip         = Kartu Indonesia Pintar
-- kis         = Kartu Indonesia Sehat
-- pkh         = Program Keluarga Harapan
-- foto        = Pas Foto
-- lainnya     = Dokumen Lainnya
