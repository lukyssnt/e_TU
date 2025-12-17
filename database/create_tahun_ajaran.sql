-- ============================================
-- Tabel Tahun Ajaran (Academic Year Management)
-- ============================================
-- Created: 2024
-- Purpose: Dynamic academic year management with validation

CREATE TABLE IF NOT EXISTS `tahun_ajaran` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `tahun_ajaran` VARCHAR(20) UNIQUE NOT NULL COMMENT 'Format: 2024/2025',
    `is_active` BOOLEAN DEFAULT 0 COMMENT 'Only one can be active',
    `tanggal_mulai` DATE NOT NULL COMMENT 'Academic year start date',
    `tanggal_akhir` DATE NOT NULL COMMENT 'Academic year end date',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_tahun` (`tahun_ajaran`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_tahun` (`tahun_ajaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migrate Existing Data
-- ============================================

-- 1. Import current active year from academic_settings
INSERT INTO `tahun_ajaran` (`tahun_ajaran`, `is_active`, `tanggal_mulai`, `tanggal_akhir`)
SELECT 
    (SELECT setting_value FROM academic_settings WHERE setting_key = 'current_academic_year'),
    1, -- Set as active
    (SELECT setting_value FROM academic_settings WHERE setting_key = 'academic_year_start'),
    (SELECT setting_value FROM academic_settings WHERE setting_key = 'academic_year_end')
WHERE EXISTS (SELECT 1 FROM academic_settings WHERE setting_key = 'current_academic_year');

-- 2. Import distinct years from kelas table (if not exist)
INSERT IGNORE INTO `tahun_ajaran` (`tahun_ajaran`, `is_active`, `tanggal_mulai`, `tanggal_akhir`)
SELECT DISTINCT 
    tahun_ajaran,
    0, -- Not active
    -- Auto-generate dates based on year format "2024/2025"
    CONCAT(SUBSTRING(tahun_ajaran, 1, 4), '-07-01') AS tanggal_mulai,
    CONCAT(SUBSTRING(tahun_ajaran, 6, 4), '-06-30') AS tanggal_akhir
FROM kelas
WHERE tahun_ajaran IS NOT NULL 
  AND tahun_ajaran != ''
  AND tahun_ajaran NOT IN (SELECT tahun_ajaran FROM tahun_ajaran);

-- ============================================
-- Success Message
-- ============================================
SELECT 
    COUNT(*) as total_tahun_ajaran,
    SUM(is_active) as active_count
FROM tahun_ajaran;
