-- Table for Asset Inventory (Inventaris Aset)
CREATE TABLE IF NOT EXISTS `aset` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_aset` VARCHAR(50) NOT NULL UNIQUE,
    `nama_barang` VARCHAR(255) NOT NULL,
    `kategori` ENUM('Elektronik', 'Furniture', 'Kendaraan', 'Alat Tulis', 'Olahraga', 'Lainnya') NOT NULL,
    `lokasi` VARCHAR(255) DEFAULT NULL,
    `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
    `dapat_dipinjam` ENUM('Ya', 'Tidak') NOT NULL DEFAULT 'Ya',
    `tanggal_perolehan` DATE NOT NULL,
    `nilai_perolehan` DECIMAL(15,2) DEFAULT 0,
    `keterangan` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_kategori` (`kategori`),
    KEY `idx_kondisi` (`kondisi`),
    KEY `idx_dapat_dipinjam` (`dapat_dipinjam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data
INSERT INTO `aset` (`kode_aset`, `nama_barang`, `kategori`, `lokasi`, `kondisi`, `dapat_dipinjam`, `tanggal_perolehan`, `nilai_perolehan`, `keterangan`) VALUES
('AST-2025-001', 'Laptop Lenovo ThinkPad', 'Elektronik', 'Ruang TU', 'Baik', 'Tidak', '2025-01-01', 8500000, 'Laptop untuk administrasi'),
('AST-2025-002', 'Proyektor Epson', 'Elektronik', 'Ruang Kelas X-1', 'Baik', 'Ya', '2024-12-15', 5000000, 'Proyektor untuk pembelajaran'),
('AST-2025-003', 'Meja Guru', 'Furniture', 'Ruang Guru', 'Baik', 'Tidak', '2024-11-10', 1500000, NULL),
('AST-2025-004', 'Kursi Siswa', 'Furniture', 'Ruang Kelas X-2', 'Rusak Ringan', 'Tidak', '2024-10-05', 350000, 'Kaki kursi goyang');
