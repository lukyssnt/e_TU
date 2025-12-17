-- Table for Student Bills (Tagihan)
CREATE TABLE IF NOT EXISTS `tagihan_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `judul_tagihan` varchar(255) NOT NULL,
  `total_tagihan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `terbayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_tagihan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('Lunas','Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `fk_tagihan_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Payment History (Riwayat Pembayaran)
CREATE TABLE IF NOT EXISTS `pembayaran_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagihan_id` int(11) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tagihan_id` (`tagihan_id`),
  CONSTRAINT `fk_pembayaran_tagihan` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihan_siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
