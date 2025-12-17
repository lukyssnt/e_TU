-- Tabel Riwayat Pegawai
DROP TABLE IF EXISTS `riwayat_pegawai`;

CREATE TABLE `riwayat_pegawai` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT(11) NOT NULL,
  `jenis_riwayat` VARCHAR(50) NOT NULL COMMENT 'Pendidikan, Jabatan, Pangkat, Penghargaan, Sanksi, Pelatihan',
  `judul` VARCHAR(255) NOT NULL COMMENT 'Misal: S1 Teknik Informatika, Kepala Sekolah, Golongan III/a',
  `instansi_lokasi` VARCHAR(255) NULL COMMENT 'Nama Kampus, Sekolah, atau Lokasi',
  `tahun_mulai` YEAR NULL,
  `tahun_selesai` YEAR NULL,
  `nomor_sk` VARCHAR(100) NULL,
  `tanggal_sk` DATE NULL,
  `keterangan` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pegawai_riwayat` (`pegawai_id`),
  KEY `idx_jenis_riwayat` (`jenis_riwayat`),
  CONSTRAINT `fk_riwayat_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
