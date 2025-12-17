CREATE TABLE IF NOT EXISTS landing_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(50) NOT NULL UNIQUE,
    content_value TEXT,
    input_type ENUM('text', 'textarea', 'image') DEFAULT 'text'
);

CREATE TABLE IF NOT EXISTS buku_tamu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    instansi VARCHAR(100),
    keperluan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pelayanan_alumni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    tahun_lulus YEAR NOT NULL,
    nisn VARCHAR(20),
    no_hp VARCHAR(20),
    jenis_layanan VARCHAR(50),
    status ENUM('Pending', 'Proses', 'Selesai', 'Ditolak') DEFAULT 'Pending',
    keterangan_admin TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO landing_content (section_key, content_value, input_type) VALUES
('hero_title', 'Sistem Administrasi Tata Usaha', 'text'),
('hero_subtitle', 'MA Al Ihsan - Layanan Digital Terintegrasi', 'text'),
('about_text', 'Platform digital untuk mempermudah layanan administrasi sekolah, meliputi kesiswaan, kepegawaian, surat menyurat, dan keuangan.', 'textarea')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);
