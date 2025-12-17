-- Update dokumen_siswa table to support multiple "Dokumen Lainnya"
USE `e_admin_tu`;

-- Add column for custom document name
ALTER TABLE `dokumen_siswa` ADD COLUMN `nama_dokumen` VARCHAR(255) NULL COMMENT 'Nama custom untuk dokumen lainnya' AFTER `jenis_dokumen`;

-- Drop existing unique constraint
ALTER TABLE `dokumen_siswa` DROP INDEX `unique_doc`;

-- Add new unique constraint that excludes 'lainnya' type
-- For lainnya, uniqueness is based on (siswa_id, jenis_dokumen, nama_dokumen)
-- For others, uniqueness is based on (siswa_id, jenis_dokumen)
-- Since MySQL doesn't support conditional unique indexes, we'll just remove the constraint
-- and handle uniqueness in application code

SELECT 'Tabel dokumen_siswa berhasil diupdate! Sekarang bisa upload multiple Dokumen Lainnya.' AS message;
