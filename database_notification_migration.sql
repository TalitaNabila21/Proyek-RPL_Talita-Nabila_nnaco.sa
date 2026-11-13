-- Migrasi Tabel Log Notifikasi WhatsApp & Pengaturan Toko
-- nnaco_sa WhatsApp Notification Feature

USE nnaco_sa;

-- 1. Tabel Settings (Pengaturan Aplikasi & WhatsApp)
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default settings jika belum ada
INSERT INTO settings (setting_key, setting_value) 
VALUES 
    ('order_notification_phone', '081953319613'),
    ('order_notify_owner', '1'),
    ('order_notify_customer', '1'),
    ('order_notify_admin', '0'),
    ('admin_notification_phones', '')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 2. Tabel Notification Logs (Pencatatan Notifikasi WhatsApp)
CREATE TABLE IF NOT EXISTS notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    recipient_type ENUM('owner', 'customer', 'admin') NOT NULL,
    recipient_phone VARCHAR(30) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    message_status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    provider_response_code VARCHAR(50) NULL,
    error_message TEXT NULL,
    attempt_count INT NOT NULL DEFAULT 1,
    last_attempt_at TIMESTAMP NULL DEFAULT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_recipient (recipient_type, recipient_phone),
    INDEX idx_status (message_status),
    CONSTRAINT fk_notification_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
