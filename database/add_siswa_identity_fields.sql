-- ================================================
-- Migration: Add Identity Fields to Siswa Table
-- ================================================

-- Add NIK column
ALTER TABLE `siswa` ADD COLUMN `nik` VARCHAR(16) NULL COMMENT 'NIK - Nomor Induk Kependudukan (16 digit)' AFTER `nisn`;

-- Add No. KK column
ALTER TABLE `siswa` ADD COLUMN `no_kk` VARCHAR(16) NULL COMMENT 'Nomor Kartu Keluarga (16 digit)' AFTER `nik`;

-- Add No. Akte Kelahiran column
ALTER TABLE `siswa` ADD COLUMN `no_akte` VARCHAR(50) NULL COMMENT 'Nomor Akte Kelahiran' AFTER `no_kk`;

-- Add Email column
ALTER TABLE `siswa` ADD COLUMN `email` VARCHAR(100) NULL COMMENT 'Email siswa (optional)' AFTER `no_hp_ortu`;

-- Add No. HP Siswa column
ALTER TABLE `siswa` ADD COLUMN `no_hp_siswa` VARCHAR(20) NULL COMMENT 'Nomor HP siswa (optional)' AFTER `email`;

-- Add indexes for faster search
ALTER TABLE `siswa` ADD INDEX `idx_nik` (`nik`);
ALTER TABLE `siswa` ADD INDEX `idx_no_kk` (`no_kk`);
