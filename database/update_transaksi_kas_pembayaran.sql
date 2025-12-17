-- Add missing columns for transaction references
ALTER TABLE `transaksi_kas`
ADD COLUMN `ref_type` VARCHAR(50) NULL AFTER `created_by`,
ADD COLUMN `ref_id` INT NULL AFTER `ref_type`;

-- Add index for performance
CREATE INDEX `idx_ref` ON `transaksi_kas` (`ref_type`, `ref_id`);
