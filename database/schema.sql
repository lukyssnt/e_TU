-- E-ADMIN TU MA AL IHSAN Database Schema
-- Sistem Informasi Intranet Staf Tata Usaha
-- Version: 1.0

CREATE DATABASE IF NOT EXISTS `e_admin_tu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `e_admin_tu`;

-- ============================================
-- TABEL MASTER
-- ============================================

-- Tabel Jabatan
CREATE TABLE IF NOT EXISTS `jabatan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_jabatan` VARCHAR(100) NOT NULL,
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nip` VARCHAR(20) UNIQUE NOT NULL,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `jabatan_id` INT,
  `status_kepegawaian` ENUM('PNS','PPPK','Honorer') NOT NULL,
  `golongan` VARCHAR(10),
  `email` VARCHAR(100),
  `no_hp` VARCHAR(15),
  `alamat` TEXT,
  `foto` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`jabatan_id`) REFERENCES `jabatan`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Kelas
CREATE TABLE IF NOT EXISTS `kelas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_kelas` VARCHAR(50) NOT NULL,
  `wali_kelas_id` INT,
  `tahun_ajaran` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`wali_kelas_id`) REFERENCES `pegawai`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Siswa
CREATE TABLE IF NOT EXISTS `siswa` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nisn` VARCHAR(20) UNIQUE NOT NULL,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `kelas_id` INT,
  `tahun_masuk` YEAR NOT NULL,
  `jenis_kelamin` ENUM('L','P') NOT NULL,
  `tempat_lahir` VARCHAR(100),
  `tanggal_lahir` DATE,
  `alamat` TEXT,
  `nama_ortu` VARCHAR(150),
  `no_hp_ortu` VARCHAR(15),
  `status` ENUM('Aktif','Pindah','Lulus','Keluar') DEFAULT 'Aktif',
  `foto` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`kelas_id`) REFERENCES `kelas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Agenda Kegiatan (Kehumasan)
CREATE TABLE IF NOT EXISTS `agenda` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `waktu_mulai` TIME,
  `waktu_selesai` TIME,
  `lokasi` VARCHAR(200),
  `penanggungjawab` VARCHAR(150),
  `status` ENUM('Akan Datang','Berlangsung','Selesai') DEFAULT 'Akan Datang',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Dokumentasi Kegiatan
CREATE TABLE IF NOT EXISTS `dokumentasi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `agenda_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`agenda_id`) REFERENCES `agenda`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Press Release
CREATE TABLE IF NOT EXISTS `press_release` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL,
  `ringkasan` TEXT,
  `isi` TEXT NOT NULL,
  `kategori` VARCHAR(100) DEFAULT 'Umum',
  `tanggal_rilis` DATE NOT NULL,
  `penulis` VARCHAR(150),
  `gambar` VARCHAR(255),
  `status` ENUM('Draft','Dipublikasi','Diarsipkan') DEFAULT 'Draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Aset
CREATE TABLE IF NOT EXISTS `aset` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `kode_aset` VARCHAR(50) UNIQUE NOT NULL,
  `nama_barang` VARCHAR(200) NOT NULL,
  `kategori` VARCHAR(100),
  `lokasi` VARCHAR(150),
  `kondisi` ENUM('Baik','Rusak Ringan','Rusak Berat') DEFAULT 'Baik',
  `tanggal_perolehan` DATE,
  `nilai_perolehan` DECIMAL(15,2),
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabel Template Surat
CREATE TABLE IF NOT EXISTS `template_surat` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_template` VARCHAR(200) NOT NULL,
  `kode_surat` VARCHAR(20) NOT NULL,
  `kategori` VARCHAR(100),
  `konten_template` TEXT NOT NULL,
  `variabel` TEXT COMMENT 'JSON array of available variables',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_role` VARCHAR(100) NOT NULL,
  `permissions` TEXT COMMENT 'Comma-separated module permissions',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `pegawai_id` INT,
  `role` VARCHAR(100),
  `permissions` TEXT COMMENT 'Comma-separated permissions',
  `last_login` TIMESTAMP NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABEL TRANSAKSI
-- ============================================

-- Tabel Surat Masuk
CREATE TABLE IF NOT EXISTS `surat_masuk` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nomor_surat` VARCHAR(100) NOT NULL,
  `tanggal_terima` DATE NOT NULL,
  `pengirim` VARCHAR(200) NOT NULL,
  `perihal` VARCHAR(255) NOT NULL,
  `sifat_surat` ENUM('Biasa','Penting','Segera','Rahasia') DEFAULT 'Biasa',
  `file_surat` VARCHAR(255),
  `status` ENUM('Belum Disposisi','Sudah Disposisi','Selesai') DEFAULT 'Belum Disposisi',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Surat Keluar
CREATE TABLE IF NOT EXISTS `surat_keluar` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nomor_surat` VARCHAR(100) UNIQUE NOT NULL,
  `tanggal_surat` DATE NOT NULL,
  `tujuan` VARCHAR(200) NOT NULL,
  `perihal` VARCHAR(255) NOT NULL,
  `template_id` INT,
  `file_surat` VARCHAR(255),
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`template_id`) REFERENCES `template_surat`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Disposisi
CREATE TABLE IF NOT EXISTS `disposisi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `surat_masuk_id` INT NOT NULL,
  `dari_pegawai_id` INT,
  `kepada_pegawai_id` INT NOT NULL,
  `instruksi` TEXT,
  `deadline` DATE,
  `status` ENUM('Pending','Proses','Selesai') DEFAULT 'Pending',
  `catatan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`surat_masuk_id`) REFERENCES `surat_masuk`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dari_pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`kepada_pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Riwayat Cuti
CREATE TABLE IF NOT EXISTS `riwayat_cuti` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT NOT NULL,
  `jenis_cuti` VARCHAR(100) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `jumlah_hari` INT NOT NULL,
  `keterangan` TEXT,
  `status` ENUM('Pending','Disetujui','Ditolak') DEFAULT 'Pending',
  `approved_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Absensi Pegawai
CREATE TABLE IF NOT EXISTS `absensi_pegawai` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_masuk` TIME,
  `jam_keluar` TIME,
  `status` ENUM('Hadir','Izin','Sakit','Alpha') NOT NULL DEFAULT 'Hadir',
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_absensi` (`pegawai_id`, `tanggal`),
  FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Peminjaman Aset
CREATE TABLE IF NOT EXISTS `peminjaman_aset` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `aset_id` INT NOT NULL,
  `nama_peminjam` VARCHAR(150) NOT NULL,
  `no_hp` VARCHAR(20),
  `tanggal_pinjam` DATE NOT NULL,
  `tanggal_kembali` DATE NOT NULL,
  `tanggal_dikembalikan` DATE,
  `keperluan` TEXT NOT NULL,
  `keterangan` TEXT,
  `status` ENUM('Dipinjam','Dikembalikan') DEFAULT 'Dipinjam',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`aset_id`) REFERENCES `aset`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Transaksi Kas
CREATE TABLE IF NOT EXISTS `transaksi_kas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tanggal` DATE NOT NULL,
  `jenis_transaksi` ENUM('Masuk','Keluar') NOT NULL,
  `kategori` VARCHAR(100),
  `keterangan` TEXT NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL,
  `saldo` DECIMAL(15,2),
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Log Aktivitas
CREATE TABLE IF NOT EXISTS `log_aktivitas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT,
  `aksi` VARCHAR(255) NOT NULL,
  `modul` VARCHAR(100),
  `detail` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert default roles
INSERT INTO `roles` (`nama_role`, `permissions`) VALUES
('Super Admin', 'all'),
('Kepala TU', 'dashboard,kepegawaian,keuangan,sarpras,kehumasan,persuratan,kesiswaan,layanan,tik'),
('Staf Keuangan', 'dashboard,keuangan,persuratan'),
('Staf Kepegawaian', 'dashboard,kepegawaian,persuratan'),
('Staf Kesiswaan', 'dashboard,kesiswaan,persuratan');

-- Insert default user (password: admin123)
INSERT INTO `users` (`username`, `password`, `role`, `permissions`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'all');

-- Insert sample jabatan
INSERT INTO `jabatan` (`nama_jabatan`, `keterangan`) VALUES
('Kepala Tata Usaha', 'Kepala bagian administrasi'),
('Bendahara', 'Pengelola keuangan'),
('Staf Kepegawaian', 'Pengelola data pegawai'),
('Staf Kesiswaan', 'Pengelola data siswa'),
('Staf Sarana Prasarana', 'Pengelola inventaris');

-- Insert sample template surat
INSERT INTO `template_surat` (`nama_template`, `kode_surat`, `kategori`, `konten_template`, `variabel`) VALUES
('Surat Keterangan Siswa Aktif', 'SKS', 'Kesiswaan', 'Yang bertanda tangan di bawah ini menerangkan bahwa:\n\nNama: {{NAMA_SISWA}}\nNISN: {{NISN}}\nKelas: {{KELAS}}\n\nAdalah benar siswa aktif di sekolah kami.', '["NAMA_SISWA","NISN","KELAS","NOMOR_SURAT","TANGGAL_SURAT"]');
