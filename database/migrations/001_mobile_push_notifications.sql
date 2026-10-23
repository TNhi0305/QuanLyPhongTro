-- Migration: 001_mobile_push_notifications.sql
-- Thêm các bảng hỗ trợ Push Notification, Lập lịch nhắc nhở và Tùy chọn thông báo
-- Đảm bảo an toàn không làm mất dữ liệu hiện có

-- 1. Bảng lưu trữ token thiết bị (Push Tokens)
CREATE TABLE IF NOT EXISTS `push_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `device_type` VARCHAR(50) DEFAULT 'mobile',
    `device_id` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_push_user` (`user_id`),
    CONSTRAINT `fk_push_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng log lập lịch nhắc nhở thanh toán (Chống gửi trùng lặp sự kiện)
CREATE TABLE IF NOT EXISTS `payment_reminder_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT NOT NULL,
    `reminder_type` VARCHAR(50) NOT NULL DEFAULT 'due_date',
    `reminder_date` DATE NOT NULL,
    `sent_to_user_id` INT NOT NULL,
    `sent_status` VARCHAR(50) NOT NULL DEFAULT 'sent',
    `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_invoice_reminder_date` (`invoice_id`, `reminder_type`, `reminder_date`),
    CONSTRAINT `fk_reminder_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng tùy chọn nhận thông báo của người dùng (Notification Preferences)
CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_user_category` (`user_id`, `category`),
    CONSTRAINT `fk_notif_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bổ sung các cột vào bảng `notifications` nếu chưa có (Thực hiện an toàn qua script)
