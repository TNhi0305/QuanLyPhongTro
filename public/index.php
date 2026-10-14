<?php
// ========================================================
// HỆ THỐNG QUẢN LÝ PHÒNG TRỌ (QuanLyPhongTro)
// Điểm khởi đầu của ứng dụng (Front Controller)
// ========================================================

// Chỉ hiển thị lỗi khi chủ động bật môi trường phát triển.
$isDevelopment = (getenv('APP_ENV') ?: 'production') === 'development';
ini_set('display_errors', $isDevelopment ? '1' : '0');
ini_set('display_startup_errors', $isDevelopment ? '1' : '0');
error_reporting(E_ALL);

// Đăng ký Autoloader cho namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\App;

// Khởi chạy ứng dụng
$app = new App();
$app->run();
