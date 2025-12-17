-- ================================================
-- Rollback: Remove Identity Fields from Siswa Table
-- ================================================

-- Drop indexes first
ALTER TABLE `siswa` DROP INDEX IF EXISTS `idx_nik`;
ALTER TABLE `siswa` DROP INDEX IF EXISTS `idx_no_kk`;

-- Drop columns
ALTER TABLE `siswa` DROP COLUMN IF EXISTS `no_hp_siswa`;
ALTER TABLE `siswa` DROP COLUMN IF EXISTS `email`;
ALTER TABLE `siswa` DROP COLUMN IF EXISTS `no_akte`;
ALTER TABLE `siswa` DROP COLUMN IF EXISTS `no_kk`;
ALTER TABLE `siswa` DROP COLUMN IF EXISTS `nik`;
