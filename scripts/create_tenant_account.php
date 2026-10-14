<?php
/**
 * Tạo tài khoản đăng nhập Expo Go và liên kết với hồ sơ người thuê.
 * Chỉ chạy qua CLI: php scripts/create_tenant_account.php <tenant_id> <username> <password>
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Script này chỉ được chạy bằng dòng lệnh.\n");
}
if ($argc !== 4) {
    fwrite(STDERR, "Cách dùng: php scripts/create_tenant_account.php <tenant_id> <username> <password>\n");
    exit(1);
}
$tenantId = filter_var($argv[1], FILTER_VALIDATE_INT);
$username = trim($argv[2]);
$password = $argv[3];
if (!$tenantId || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) || strlen($password) < 8) {
    fwrite(STDERR, "tenant_id không hợp lệ; username 3-50 ký tự (chữ/số/._-); mật khẩu tối thiểu 8 ký tự.\n");
    exit(1);
}
require_once __DIR__ . '/../app/Helpers/Database.php';
try {
    $pdo = App\Helpers\Database::getConnection();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id, fullname, phone, email, user_id FROM tenants WHERE id = ? FOR UPDATE');
    $stmt->execute([$tenantId]);
    $tenant = $stmt->fetch();
    if (!$tenant) throw new RuntimeException('Không tìm thấy hồ sơ người thuê.');
    if (!empty($tenant['user_id'])) throw new RuntimeException('Hồ sơ này đã được liên kết với một tài khoản.');
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) throw new RuntimeException('Tên đăng nhập đã tồn tại.');
    $email = $tenant['email'] ?: null;
    if ($email) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $email = null; // Không làm hỏng quy trình nếu email đã dùng bởi tài khoản khác.
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, fullname, phone, email, status) VALUES (?, ?, 'tenant', ?, ?, ?, 1)");
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $tenant['fullname'], $tenant['phone'], $email]);
    $userId = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare('UPDATE tenants SET user_id = ? WHERE id = ?');
    $stmt->execute([$userId, $tenantId]);
    $pdo->commit();
    echo "Tạo tài khoản khách thuê thành công.\nTenant ID: {$tenantId}\nUsername: {$username}\nUser ID: {$userId}\n";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Không thể tạo tài khoản: ' . $e->getMessage() . "\n");
    exit(1);
}
