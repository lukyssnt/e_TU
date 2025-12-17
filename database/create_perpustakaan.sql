-- SQL untuk membuat tabel perpustakaan
-- Jalankan di phpMyAdmin

USE `e_admin_tu`;

-- Tabel Buku
CREATE TABLE IF NOT EXISTS `buku` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `kode_buku` VARCHAR(50) NOT NULL UNIQUE,
  `judul` VARCHAR(255) NOT NULL,
  `pengarang` VARCHAR(100) NOT NULL,
  `penerbit` VARCHAR(100) NOT NULL,
  `tahun_terbit` YEAR NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `lokasi_rak` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Peminjaman Buku
CREATE TABLE IF NOT EXISTS `peminjaman_buku` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `buku_id` INT NOT NULL,
  `siswa_id` INT NOT NULL,
  `tanggal_pinjam` DATE NOT NULL,
  `tanggal_kembali_rencana` DATE NOT NULL,
  `tanggal_kembali_realisasi` DATE DEFAULT NULL,
  `status` ENUM('Dipinjam', 'Kembali', 'Hilang') NOT NULL DEFAULT 'Dipinjam',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`buku_id`) REFERENCES `buku`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contoh Data Buku
INSERT INTO `buku` (`kode_buku`, `judul`, `pengarang`, `penerbit`, `tahun_terbit`, `kategori`, `stok`, `lokasi_rak`) VALUES
('B001', 'Matematika Kelas X', 'Kemendikbud', 'Pusat Kurikulum', 2021, 'Pelajaran', 50, 'R-01'),
('B002', 'Bahasa Indonesia Kelas X', 'Kemendikbud', 'Pusat Kurikulum', 2021, 'Pelajaran', 45, 'R-01'),
('B003', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'Fiksi', 5, 'F-02');

SELECT 'Tabel perpustakaan berhasil dibuat!' AS message;
