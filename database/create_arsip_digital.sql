-- SQL untuk membuat tabel arsip_digital
-- Jalankan di phpMyAdmin

USE `e_admin_tu`;

-- Tabel Arsip Digital
CREATE TABLE IF NOT EXISTS `arsip_digital` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `kode_arsip` VARCHAR(50) NOT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT,
  `jenis` ENUM('Surat Masuk','Surat Keluar','Dokumen','Lainnya') NOT NULL DEFAULT 'Dokumen',
  `kategori` VARCHAR(100),
  `tanggal_arsip` DATE NOT NULL,
  `file_arsip` VARCHAR(255),
  `lokasi_fisik` VARCHAR(150) COMMENT 'Lokasi penyimpanan fisik',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_kode` (`kode_arsip`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `arsip_digital` (`kode_arsip`, `judul`, `jenis`, `kategori`, `tanggal_arsip`, `lokasi_fisik`) VALUES
('SM-20241201-001', 'Surat Undangan Rapat Dinas Pendidikan', 'Surat Masuk', 'Undangan', '2024-12-01', 'Lemari A, Rak 1'),
('SK-20241202-001', 'Surat Keterangan Siswa Aktif - Ahmad', 'Surat Keluar', 'Keterangan', '2024-12-02', 'Lemari A, Rak 2'),
('DOK-20241203-001', 'Proposal Kegiatan OSIS 2024', 'Dokumen', 'Proposal', '2024-12-03', 'Lemari B, Rak 1');

SELECT 'Tabel arsip_digital berhasil dibuat!' AS message;
