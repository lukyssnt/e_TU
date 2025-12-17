-- Tabel RAB
DROP TABLE IF EXISTS `rab`;

CREATE TABLE `rab` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(50) NOT NULL,
  `uraian` TEXT NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `volume` INT NOT NULL,
  `satuan` VARCHAR(50) NOT NULL,
  `harga_satuan` DECIMAL(15,2) NOT NULL,
  `jumlah` DECIMAL(15,2) NOT NULL,
  `tahun` YEAR NOT NULL,
  `status` ENUM('Draft','Disetujui','Terealisasi') DEFAULT 'Draft',
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
