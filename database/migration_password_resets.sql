-- Creates the table required by the forgot-password OTP flow.
-- Safe to run against an existing database.
USE `resc_qr`;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `otp_code` VARCHAR(10) NOT NULL,
    `otp_hash` VARCHAR(255) NOT NULL,
    `user_type` VARCHAR(20) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_password_resets_email_created` (`email`, `created_at`),
    INDEX `idx_password_resets_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
