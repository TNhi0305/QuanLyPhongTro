<?php

namespace App\Helpers;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Lấy thể hiện kết nối PDO (Singleton Pattern)
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Ghi log lỗi kết nối an toàn không lộ thông tin mật khẩu
                $logDir = __DIR__ . '/../../storage/logs';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0777, true);
                }
                @file_put_contents(
                    $logDir . '/database_errors.log',
                    "[" . date('Y-m-d H:i:s') . "] Lỗi kết nối CSDL: " . $e->getMessage() . PHP_EOL,
                    FILE_APPEND
                );
                die("<div style='font-family: Arial, sans-serif; padding: 20px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 6px; max-width: 650px; margin: 40px auto;'>"
                    . "<h3 style='margin-top:0;'>⚠️ Lỗi kết nối Cơ sở dữ liệu MySQL</h3>"
                    . "<p>Hệ thống không thể kết nối tới MySQL. Vui lòng kiểm tra lại:</p>"
                    . "<ul>"
                    . "<li>WAMP64 đã bật và biểu tượng có màu xanh lá.</li>"
                    . "<li>Dịch vụ MySQL (cổng 3306) đang hoạt động.</li>"
                    . "<li>Đã import file <code>database/database.sql</code> vào database <code>quanlyphongtro</code>.</li>"
                    . "<li>Kiểm tra cấu hình tài khoản trong <code>config/database.php</code>.</li>"
                    . "</ul>"
                    . "</div>");
            }
        }

        return self::$instance;
    }

    /**
     * Bắt đầu một Transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Xác nhận Transaction
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Hủy bỏ Transaction khi có lỗi
     */
    public static function rollBack(): bool
    {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }
}
