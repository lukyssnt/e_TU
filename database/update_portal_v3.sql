-- Add Finance Feature Status
INSERT INTO landing_content (section_key, content_value, input_type) VALUES
('finance_feature_status', '1', 'select')
ON DUPLICATE KEY UPDATE input_type = VALUES(input_type);
