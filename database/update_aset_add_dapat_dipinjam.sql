-- Add missing column 'dapat_dipinjam' to aset table
ALTER TABLE `aset` 
ADD COLUMN `dapat_dipinjam` ENUM('Ya', 'Tidak') NOT NULL DEFAULT 'Ya' 
AFTER `kondisi`;
