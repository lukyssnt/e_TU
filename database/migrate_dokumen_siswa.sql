-- ================================================
-- Migration: dokumen_siswa table
-- ================================================

-- Drop unique constraint if exists (for 'lainnya' multiple uploads)
ALTER TABLE `dokumen_siswa` DROP INDEX IF EXISTS `unique_doc`;

-- Add nama_dokumen column if not exists (for 'lainnya' description)
ALTER TABLE `dokumen_siswa` 
ADD COLUMN IF NOT EXISTS `nama_dokumen` VARCHAR(255) DEFAULT NULL COMMENT 'Nama untuk dokumen lainnya' AFTER `jenis_dokumen`;

-- Add tahun_lulus to siswa table if not exists
ALTER TABLE `siswa` 
ADD COLUMN IF NOT EXISTS `tahun_lulus` INT NULL DEFAULT NULL COMMENT 'Tahun kelulusan' AFTER `tahun_masuk`;

-- Show success message
SELECT 'Migration completed! dokumen_siswa table updated.' AS message;
