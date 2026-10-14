<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Database;
use App\Middleware\AuthMiddleware;

class UserController extends Controller
{
    public function __construct() { AuthMiddleware::requireAdmin(); }

    public function index(): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $like = '%' . $keyword . '%';
        $sql = "SELECT u.id, u.username, u.fullname, u.email, u.phone, u.role, u.status, u.created_at
                FROM users u
                WHERE (:kw = '' OR u.username LIKE :a OR u.fullname LIKE :b OR u.email LIKE :c OR u.phone LIKE :d)
                ORDER BY u.role ASC, u.id DESC";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['kw'=>$keyword, 'a'=>$like, 'b'=>$like, 'c'=>$like, 'd'=>$like]);
        $this->view('users.index', ['users'=>$stmt->fetchAll(), 'keyword'=>$keyword], 'admin');
    }

    public function toggleStatus(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;
        $current = auth_user();
        if ($current && (int)$current['id'] === $id) {
            set_flash('danger', 'Bạn không thể tự khóa tài khoản đang đăng nhập.');
            $this->redirect('/users');
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, status FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $target = $stmt->fetch();
        if (!$target) {
            set_flash('danger', 'Không tìm thấy tài khoản.');
            $this->redirect('/users');
        }
        $status = (int)$target['status'] === 1 ? 0 : 1;
        $pdo->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
        log_activity($status ? 'Mở khóa tài khoản' : 'Khóa tài khoản', 'users', $id, 'Tài khoản ID ' . $id);
        set_flash('success', $status ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.');
        $this->redirect('/users');
    }
}
