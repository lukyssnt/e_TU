-- Tabel untuk Maintenance Aset
CREATE TABLE IF NOT EXISTS maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aset_id INT NOT NULL,
    kode_maintenance VARCHAR(50) UNIQUE NOT NULL,
    tanggal_maintenance DATE NOT NULL,
    jenis_maintenance VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    biaya DECIMAL(15,2) DEFAULT 0,
    teknisi VARCHAR(100),
    status ENUM('Proses', 'Selesai') DEFAULT 'Proses',
    tanggal_selesai DATE NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aset_id) REFERENCES aset(id) ON DELETE CASCADE,
    INDEX idx_aset_id (aset_id),
    INDEX idx_status (status),
    INDEX idx_tanggal (tanggal_maintenance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO maintenance (aset_id, kode_maintenance, tanggal_maintenance, jenis_maintenance, deskripsi, biaya, teknisi, status) 
SELECT 
    a.id,
    CONCAT('MNT-', YEAR(CURDATE()), '-', LPAD(1, 4, '0')) as kode_maintenance,
    CURDATE() as tanggal_maintenance,
    'Perbaikan Rutin' as jenis_maintenance,
    CONCAT('Maintenance untuk ', a.nama_barang) as deskripsi,
    500000 as biaya,
    'Tim Teknis' as teknisi,
    'Proses' as status
FROM aset a 
WHERE a.kondisi = 'Rusak'
LIMIT 1;
