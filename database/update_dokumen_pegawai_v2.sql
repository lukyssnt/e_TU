-- Update dokumen_pegawai table to support multiple "Dokumen Lainnya"
USE `e_admin_tu`;

-- Add column for custom document name
ALTER TABLE `dokumen_pegawai` ADD COLUMN `nama_dokumen` VARCHAR(255) NULL COMMENT 'Nama custom untuk dokumen lainnya' AFTER `jenis_dokumen`;

-- Drop existing unique constraint if exists
ALTER TABLE `dokumen_pegawai` DROP INDEX `unique_doc_pegawai`;

SELECT 'Tabel dokumen_pegawai berhasil diupdate! Sekarang bisa upload multiple Dokumen Lainnya.' AS message;
