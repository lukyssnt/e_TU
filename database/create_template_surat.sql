-- Script untuk membuat table template_surat jika belum ada
-- Jalankan di phpMyAdmin: localhost/phpmyadmin

USE `e_admin_tu`;

-- Tabel Template Surat
CREATE TABLE IF NOT EXISTS `template_surat` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama_template` VARCHAR(200) NOT NULL,
  `kode_surat` VARCHAR(20) NOT NULL,
  `kategori` VARCHAR(100) DEFAULT 'Umum',
  `konten_template` TEXT NOT NULL,
  `variabel` TEXT COMMENT 'JSON array of available variables',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample template jika belum ada
INSERT IGNORE INTO `template_surat` (`id`, `nama_template`, `kode_surat`, `kategori`, `konten_template`, `variabel`) VALUES
(1, 'Surat Keterangan Siswa Aktif', 'SKS', 'Kesiswaan', 
'<div style="text-align: center; margin-bottom: 20px;">
<h1 style="margin: 0; font-size: 18pt;">SURAT KETERANGAN SISWA AKTIF</h1>
<p style="margin: 5px 0;">Nomor: {{NOMOR_SURAT}}</p>
</div>

<p style="text-indent: 40px;">Yang bertanda tangan di bawah ini, Kepala MA Al Ihsan, menerangkan bahwa:</p>

<table style="margin-left: 40px; margin-bottom: 20px;">
<tr><td width="150">Nama</td><td>: {{NAMA_SISWA}}</td></tr>
<tr><td>NISN</td><td>: {{NISN}}</td></tr>
<tr><td>Kelas</td><td>: {{KELAS}}</td></tr>
</table>

<p style="text-indent: 40px;">Adalah benar siswa/siswi aktif di MA Al Ihsan pada tahun pelajaran 2024/2025.</p>

<p style="text-indent: 40px;">Demikian surat keterangan ini dibuat untuk keperluan <strong>{{KEPERLUAN}}</strong>.</p>

<table width="100%" style="margin-top: 40px;">
<tr>
<td width="60%"></td>
<td style="text-align: center;">
<p style="margin: 0;">Kota, {{TANGGAL_SURAT}}</p>
<p style="margin: 0;">Kepala Madrasah,</p>
<br><br><br>
<p style="margin: 0; font-weight: bold; text-decoration: underline;">Nama Kepala Sekolah</p>
<p style="margin: 0;">NIP. 123456789</p>
</td>
</tr>
</table>', 
'["NOMOR_SURAT","TANGGAL_SURAT","NAMA_SISWA","NISN","KELAS","KEPERLUAN"]'),

(2, 'Surat Izin Keluar Siswa', 'SIK', 'Kesiswaan', 
'<div style="text-align: center; margin-bottom: 20px;">
<h1 style="margin: 0; font-size: 18pt;">SURAT IZIN KELUAR</h1>
<p style="margin: 5px 0;">Nomor: {{NOMOR_SURAT}}</p>
</div>

<p>Yang bertanda tangan di bawah ini, Guru Piket MA Al Ihsan, memberikan izin kepada:</p>

<table style="margin-left: 40px; margin-bottom: 20px;">
<tr><td width="150">Nama</td><td>: {{NAMA_SISWA}}</td></tr>
<tr><td>NISN</td><td>: {{NISN}}</td></tr>
<tr><td>Kelas</td><td>: {{KELAS}}</td></tr>
<tr><td>Alasan</td><td>: {{KEPERLUAN}}</td></tr>
</table>

<p>Untuk meninggalkan sekolah pada hari ini, {{TANGGAL_SURAT}}.</p>

<table width="100%" style="margin-top: 40px;">
<tr>
<td style="text-align: center;">
<p style="margin: 0;">Guru Piket,</p>
<br><br><br>
<p style="margin: 0; font-weight: bold;">____________________</p>
</td>
</tr>
</table>', 
'["NOMOR_SURAT","TANGGAL_SURAT","NAMA_SISWA","NISN","KELAS","KEPERLUAN"]');

SELECT 'Table template_surat berhasil dibuat!' AS message;
