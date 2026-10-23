<?php

use App\Helpers\Database;

/**
 * Lấy đường dẫn gốc (Base URL) của ứng dụng
 */
function base_url(string $path = ''): string
{
    $config = require __DIR__ . '/../../config/app.php';
    if (!empty($config['base_url'])) {
        $base = rtrim($config['base_url'], '/');
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $baseDir = rtrim(str_replace('\\', '/', $scriptDir), '/');
        $base = $protocol . $host . $baseDir;
    }

    if ($path === '') {
        return $base;
    }
    return $base . '/' . ltrim($path, '/');
}

/**
 * Tạo URL đến một route
 */
function url(string $route = '', array $params = []): string
{
    $route = trim($route, '/');
    $url = base_url($route);
    if (!empty($params)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
    }
    return $url;
}

/**
 * Tạo URL tài nguyên tĩnh (CSS, JS, Hình ảnh)
 */
function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Tạo URL file upload
 */
function upload_url(?string $path = null): string
{
    if (empty($path)) {
        return asset('images/no-image.png');
    }
    return base_url('uploads/' . ltrim($path, '/'));
}

/**
 * Escape chuỗi để phòng chống tấn công XSS
 */
function e(?string $string): string
{
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Định dạng tiền tệ VNĐ
 */
function format_currency(float|int|string|null $amount): string
{
    $val = (float)($amount ?? 0);
    return number_format($val, 0, ',', '.') . ' đ';
}

/**
 * Định dạng ngày (dd/mm/YYYY)
 */
function format_date(?string $date, string $format = 'd/m/Y'): string
{
    if (empty($date)) {
        return '-';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '-';
}

/**
 * Định dạng ngày giờ
 */
function format_datetime(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (empty($datetime)) {
        return '-';
    }
    $timestamp = strtotime($datetime);
    return $timestamp ? date($format, $timestamp) : '-';
}

/**
 * Chuyển hướng trang
 */
function redirect(string $url): void
{
    // Nếu là route tương đối, prepend base_url
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = url($url);
    }
    header("Location: " . $url);
    exit;
}

/**
 * Tạo CSRF Token
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Tạo thẻ input CSRF
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Kiểm tra tính hợp lệ của CSRF Token
 */
function verify_csrf_token(?string $token = null): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $token ?? ($_POST['_csrf_token'] ?? '');
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Đặt thông báo Flash (hiển thị 1 lần sau khi redirect)
 */
function set_flash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Lấy thông báo Flash và xóa khỏi session
 */
function get_flash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Lấy thông tin người dùng đang đăng nhập
 */
function auth_user(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user'] ?? null;
}

/**
 * Kiểm tra đã đăng nhập chưa
 */
function is_logged_in(): bool
{
    return auth_user() !== null;
}

/**
 * Kiểm tra người dùng có quyền Admin không
 */
function is_admin(): bool
{
    $user = auth_user();
    return $user && ($user['role'] ?? '') === 'admin';
}

/**
 * Kiểm tra người dùng có phải Người thuê (Tenant) không
 */
function is_tenant(): bool
{
    $user = auth_user();
    return $user && ($user['role'] ?? '') === 'tenant';
}

/**
 * Lấy tenant_id của người thuê hiện tại (nếu role là tenant)
 */
function current_tenant_id(): ?int
{
    $user = auth_user();
    if (!$user || $user['role'] !== 'tenant') {
        return null;
    }
    if (isset($_SESSION['tenant_id'])) {
        return (int)$_SESSION['tenant_id'];
    }
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM tenants WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $tid = $stmt->fetchColumn();
        if ($tid) {
            $_SESSION['tenant_id'] = (int)$tid;
            return (int)$tid;
        }
    } catch (Exception $e) {
        // Ignored
    }
    return null;
}

/**
 * Ghi lại nhật ký hoạt động (activity_logs)
 */
function log_activity(string $action, string $entityType, ?int $entityId = null, ?string $details = null): void
{
    try {
        $pdo = Database::getConnection();
        $user = auth_user();
        $userId = $user ? $user['id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $action, $entityType, $entityId, $details, $ip]);
    } catch (Exception $e) {
        // Ghi log vào file nếu lỗi DB
        @file_put_contents(__DIR__ . '/../../storage/logs/activity_error.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}

/**
 * Lấy giá trị cài đặt hệ thống
 */
function get_setting(string $key, string $default = ''): string
{
    static $settingsCache = null;
    if ($settingsCache === null) {
        $settingsCache = [];
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
            while ($row = $stmt->fetch()) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Ignored
        }
    }
    return $settingsCache[$key] ?? $default;
}

/**
 * Xử lý tải file lên an toàn
 */
function upload_image(array $file, string $subfolder = 'rooms'): ?string
{
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $config = require __DIR__ . '/../../config/app.php';
    if ($file['size'] > $config['upload_max_size']) {
        throw new Exception("Kích thước file không được vượt quá 5MB.");
    }

    // Kiểm tra định dạng đuôi file
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $config['allowed_image_exts'])) {
        throw new Exception("Định dạng file không hợp lệ. Chỉ chấp nhận JPG, PNG, WEBP, GIF.");
    }

    // Kiểm tra MIME type thực tế bằng finfo để chống file mã độc đổi đuôi
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $config['allowed_image_types'])) {
        throw new Exception("Loại file không được phép tải lên.");
    }

    // Tạo tên file ngẫu nhiên an toàn
    $targetDir = __DIR__ . '/../../public/uploads/' . trim($subfolder, '/');
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0777, true);
    }

    $newFileName = uniqid('img_', true) . '.' . $ext;
    $targetPath = $targetDir . '/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return trim($subfolder, '/') . '/' . $newFileName;
    }

    throw new Exception("Không thể lưu file tải lên.");
}

/**
 * Render View với Layout
 */
function view(string $viewPath, array $data = [], string $layout = 'admin'): void
{
    extract($data);

    // Chuyển view path từ dot notation (ví dụ: 'rooms.index') thành file path
    $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

    if (!file_exists($viewFile)) {
        die("View không tồn tại: " . htmlspecialchars($viewPath));
    }

    // Bắt đầu buffer để lấy nội dung content view
    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    // Nếu không dùng layout (view độc lập như in ấn hoặc modal)
    if ($layout === 'none' || empty($layout)) {
        echo $content;
        return;
    }

    $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
    if (file_exists($layoutFile)) {
        require $layoutFile;
    } else {
        echo $content;
    }
}

/**
 * Trích xuất mã định danh ngân hàng (BIN hoặc ShortName) cho VietQR
 */
function get_vietqr_bank_id(string $bankName = ''): string
{
    $bn = mb_strtoupper(trim($bankName), 'UTF-8');
    if ($bn === '') {
        $bn = mb_strtoupper(get_setting('bank_name', 'MB'), 'UTF-8');
    }
    if (strpos($bn, 'MB') !== false || strpos($bn, 'QUÂN ĐỘI') !== false) return 'MB';
    if (strpos($bn, 'VIETCOMBANK') !== false || strpos($bn, 'VCB') !== false) return 'VCB';
    if (strpos($bn, 'TECHCOMBANK') !== false || strpos($bn, 'TCB') !== false) return 'TCB';
    if (strpos($bn, 'VIETINBANK') !== false || strpos($bn, 'CTG') !== false) return 'ICB';
    if (strpos($bn, 'BIDV') !== false) return 'BIDV';
    if (strpos($bn, 'ACB') !== false) return 'ACB';
    if (strpos($bn, 'VPBANK') !== false || strpos($bn, 'VPB') !== false) return 'VPB';
    if (strpos($bn, 'TPBANK') !== false || strpos($bn, 'TPB') !== false) return 'TPB';
    if (strpos($bn, 'SACOMBANK') !== false || strpos($bn, 'STB') !== false) return 'STB';
    if (strpos($bn, 'AGRIBANK') !== false || strpos($bn, 'VBA') !== false) return 'VBA';
    if (strpos($bn, 'HDBANK') !== false || strpos($bn, 'HDB') !== false) return 'HDB';
    if (strpos($bn, 'VIB') !== false) return 'VIB';
    if (strpos($bn, 'SHB') !== false) return 'SHB';
    if (strpos($bn, 'OCB') !== false) return 'OCB';
    if (strpos($bn, 'MSB') !== false) return 'MSB';
    if (strpos($bn, 'SEABANK') !== false) return 'SEAB';
    if (strpos($bn, 'LPBANK') !== false || strpos($bn, 'LIENVIETPOSTBANK') !== false) return 'LPB';
    return 'MB';
}

/**
 * Tạo URL mã QR thanh toán chuẩn VietQR
 */
function get_vietqr_url(?string $accountNo = null, ?string $accountName = null, float|int $amount = 0, string $addInfo = '', ?string $bankName = null, ?string $template = null): string
{
    $accountNo = $accountNo ?: get_setting('bank_account_number', '090123456789');
    $accountName = $accountName ?: get_setting('bank_account_name', 'CHU NHA TRO AN BINH');
    $bankName = $bankName ?: get_setting('bank_name', 'MB Bank');
    $template = $template ?: get_setting('bank_qr_template', 'compact2');

    if (!in_array($template, ['compact', 'compact2', 'qr_only', 'print'])) {
        $template = 'compact2';
    }

    $bankId = get_vietqr_bank_id($bankName);
    $cleanAccountNo = preg_replace('/[^0-9A-Za-z]/', '', $accountNo);

    $url = "https://img.vietqr.io/image/{$bankId}-{$cleanAccountNo}-{$template}.png";
    $params = [];
    if ($amount > 0) {
        $params['amount'] = (int)round($amount);
    }
    if ($addInfo !== '') {
        $params['addInfo'] = $addInfo;
    }
    if ($accountName !== '') {
        $params['accountName'] = $accountName;
    }
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

