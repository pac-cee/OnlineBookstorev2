-- Add role column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user';

-- Add status column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active';

-- Add stock_threshold to books table if it doesn't exist
ALTER TABLE books ADD COLUMN IF NOT EXISTS stock_threshold INT DEFAULT 5;

-- Create config table
CREATE TABLE IF NOT EXISTS config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(50) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Insert default config values
INSERT INTO config (`key`, value, description) 
VALUES 
    ('default_stock_threshold', '5', 'Default threshold for low stock notifications'),
    ('sales_tax_percentage', '10', 'Sales tax percentage'),
    ('require_email_verification', 'false', 'Whether new users require email verification')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Create initial admin user
INSERT INTO users (username, email, password, role)
VALUES ('admin', 'admin@example.com', SHA2('StrongPassword123', 256), 'admin')
ON DUPLICATE KEY UPDATE role = 'admin';