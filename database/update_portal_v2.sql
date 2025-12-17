-- Add Image Content Keys
INSERT INTO landing_content (section_key, content_value, input_type) VALUES
('hero_image', 'images/hero_default.jpg', 'image'),
('about_image', 'images/about_default.jpg', 'image')
ON DUPLICATE KEY UPDATE input_type = VALUES(input_type);

-- Update Alumni Table
-- We will add 'deskripsi' and ensure 'jenis_layanan' acts as 'keperluan' (VARCHAR)
ALTER TABLE pelayanan_alumni ADD COLUMN deskripsi TEXT AFTER jenis_layanan;
ALTER TABLE pelayanan_alumni MODIFY COLUMN jenis_layanan VARCHAR(100); -- Relax from previous setup if needed, or if it was VARCHAR already it's fine.
