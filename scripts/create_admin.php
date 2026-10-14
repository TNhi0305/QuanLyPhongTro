<?php
// Chạy trong Terminal tại thư mục gốc: php scripts/create_admin.php ten_dang_nhap "MatKhauManh!"
// Không tạo mật khẩu mặc định. Chỉ chạy từ CLI.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script này chỉ được chạy từ Terminal.');
}
require_once __DIR__ . '/../app/Helpers/Database.php';

use App\Helpers\Database;

$username = trim($argv[1] ?? '');
$password = (string)($argv[2] ?? '');
if ($username === '' || $password === '') {
    fwrite(STDERR, "Cách dùng: php scripts/create_admin.php <ten_dang_nhap> <mat_khau>\n");
    fwrite(STDERR, "Mật khẩu nên có ít nhất 12 ký tự. Không dùng mật khẩu mẫu yếu.\n");
    exit(2);
}
if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
    fwrite(STDERR, "Tên đăng nhập phải dài 3-50 ký tự, chỉ gồm chữ, số, dấu chấm, gạch dưới hoặc gạch ngang.\n");
    exit(2);
}
if (strlen($password) < 12) {
    fwrite(STDERR, "Mật khẩu phải có ít nhất 12 ký tự.\n");
    exit(2);
}
try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $id = $stmt->fetchColumn();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($id) {
        $update = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', status = 1 WHERE id = ?");
        $update->execute([$hash, $id]);
        echo "Đã đặt lại mật khẩu và quyền admin cho tài khoản {$username}.\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, role, fullname, status, created_at) VALUES (?, ?, 'admin', 'Quản trị viên', 1, NOW())");
        $insert->execute([$username, $hash]);
        echo "Đã tạo tài khoản admin {$username}.\n";
    }
    echo "Không chia sẻ mật khẩu và hãy xóa/giữ kín lịch sử Terminal nếu có lưu lệnh.\n";
} catch (Throwable $e) {
    error_log('create_admin: '.$e->getMessage());
    fwrite(STDERR, "Không thể tạo tài khoản. Kiểm tra cấu hình DB và import database.sql.\n");
    exit(1);
}
