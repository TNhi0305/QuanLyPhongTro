<?php
/**
 * Safe database migration for mobile push notifications & reminders.
 * Preserves all existing tables and data.
 */
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = App\Helpers\Database::getConnection();
    echo "Connecting to MySQL...\n";

    // 1. Create push_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `push_tokens` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `token` VARCHAR(255) NOT NULL UNIQUE,
            `device_type` VARCHAR(50) DEFAULT 'mobile',
            `device_id` VARCHAR(100) DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_push_user` (`user_id`),
            CONSTRAINT `fk_push_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Checked/created push_tokens table.\n";

    // 2. Create payment_reminder_logs table
    $pdo->exec("
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
    ");
    echo "Checked/created payment_reminder_logs table.\n";

    // 2b. Create notification_preferences table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notification_preferences` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `category` VARCHAR(50) NOT NULL,
            `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_user_category` (`user_id`, `category`),
            CONSTRAINT `fk_notif_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Checked/created notification_preferences table.\n";

    // Check is_active in push_tokens
    $pushTokenCols = $pdo->query("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'push_tokens'
    ")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_active', $pushTokenCols, true)) {
        $pdo->exec("ALTER TABLE `push_tokens` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `device_id`");
        echo "Added is_active to push_tokens.\n";
    }

    // 3. Add columns to notifications safely
    $existingCols = $pdo->query("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('target_user_id', $existingCols, true)) {
        $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `target_user_id` INT DEFAULT NULL AFTER `target_tenant_id`");
        echo "Added target_user_id to notifications.\n";
    }

    if (!in_array('is_read', $existingCols, true)) {
        $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0 AFTER `created_by`");
        echo "Added is_read to notifications.\n";
    }

    if (!in_array('reference_type', $existingCols, true)) {
        $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `reference_type` VARCHAR(50) DEFAULT NULL AFTER `is_read`");
        echo "Added reference_type to notifications.\n";
    }

    if (!in_array('reference_id', $existingCols, true)) {
        $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `reference_id` INT DEFAULT NULL AFTER `reference_type`");
        echo "Added reference_id to notifications.\n";
    }

    if (!in_array('data_payload', $existingCols, true)) {
        $pdo->exec("ALTER TABLE `notifications` ADD COLUMN `data_payload` TEXT DEFAULT NULL AFTER `reference_id`");
        echo "Added data_payload to notifications.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
