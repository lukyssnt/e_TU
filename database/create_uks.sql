-- SQL untuk membuat tabel uks
-- Jalankan di phpMyAdmin

USE `e_admin_tu`;

-- Tabel Obat
CREATE TABLE IF NOT EXISTS `obat` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_obat` VARCHAR(100) NOT NULL,
  `jenis` VARCHAR(50) NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `satuan` VARCHAR(20) NOT NULL,
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Catatan Kesehatan
CREATE TABLE IF NOT EXISTS `catatan_kesehatan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `siswa_id` INT NOT NULL,
  `tanggal_periksa` DATE NOT NULL,
  `keluhan` TEXT NOT NULL,
  `diagnosa` VARCHAR(255) NOT NULL,
  `tindakan` TEXT NOT NULL,
  `obat_diberikan` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contoh Data Obat
INSERT INTO `obat` (`nama_obat`, `jenis`, `stok`, `satuan`, `keterangan`) VALUES
('Paracetamol', 'Tablet', 100, 'strip', 'Obat penurun panas dan pereda nyeri'),
('Betadine', 'Cair', 10, 'botol', 'Obat antiseptik luka'),
('Minyak Kayu Putih', 'Cair', 15, 'botol', 'Untuk masuk angin dan gatal'),
('Hansaplast', 'Alat', 50, 'pcs', 'Plester luka');

SELECT 'Tabel uks berhasil dibuat!' AS message;
