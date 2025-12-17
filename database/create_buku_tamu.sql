CREATE TABLE `buku_tamu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `instansi` varchar(200) DEFAULT NULL,
  `keperluan` text NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanggal_berkunjung` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
