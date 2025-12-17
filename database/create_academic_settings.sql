-- Academic Settings Table
-- Stores system-wide academic configuration

CREATE TABLE IF NOT EXISTS `academic_settings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'number', 'date', 'boolean') DEFAULT 'text',
    `description` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default academic settings
INSERT INTO `academic_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('current_academic_year', '2024/2025', 'text', 'Tahun ajaran aktif saat ini'),
('semester', '1', 'number', 'Semester aktif (1 atau 2)'),
('academic_year_start', '2024-07-01', 'date', 'Tanggal mulai tahun ajaran'),
('academic_year_end', '2025-06-30', 'date', 'Tanggal akhir tahun ajaran'),
('show_previous_debts', '1', 'boolean', 'Tampilkan tunggakan tahun lalu secara terpisah');
