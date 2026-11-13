-- Migrasi Fitur Login OTP WhatsApp Fonnte untuk nnaco_sa
-- 1. Tambah kolom phone ke tabel users jika belum ada
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) NULL UNIQUE AFTER `email`;

-- 2. Buat tabel login_otps
CREATE TABLE IF NOT EXISTS `login_otps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `otp_hash` VARCHAR(255) NOT NULL,
    `purpose` VARCHAR(50) NOT NULL DEFAULT 'login',
    `expires_at` DATETIME NOT NULL,
    `attempt_count` INT NOT NULL DEFAULT 0,
    `max_attempts` INT NOT NULL DEFAULT 5,
    `used_at` DATETIME NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_phone_purpose` (`phone_number`, `purpose`, `used_at`),
    INDEX `idx_expires` (`expires_at`),
    CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
