<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Notification;
use App\Models\Tenant;
use Exception;

class NotificationController extends Controller
{
    private Notification $notifModel;
    private Tenant $tenantModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->notifModel = new Notification();
        $this->tenantModel = new Tenant();
    }

    public function index(): void
    {
        $notifications = $this->notifModel->allForAdmin();
        $tenants = $this->tenantModel->all('fullname ASC');

        $this->view('notifications.index', [
            'notifications' => $notifications,
            'tenants'       => $tenants
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $type = $_POST['type'] ?? 'general';
        $targetTenantId = !empty($_POST['target_tenant_id']) ? (int)$_POST['target_tenant_id'] : null;

        if (empty($title) || empty($content)) {
            set_flash('danger', 'Vui lòng điền đầy đủ tiêu đề và nội dung thông báo.');
            $this->redirect('/notifications');
        }

        try {
            $user = auth_user();
            $id = $this->notifModel->create([
                'title'            => $title,
                'content'          => $content,
                'type'             => $type,
                'target_tenant_id' => $targetTenantId,
                'created_by'       => $user['id'] ?? null
            ]);

            log_activity('Đăng thông báo', 'notifications', $id, "Đăng thông báo: {$title}");
            set_flash('success', "Gửi thông báo thành công!");
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/notifications');
    }

    public function delete(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $notif = $this->notifModel->find($id);
        if ($notif) {
            $this->notifModel->delete($id);
            log_activity('Xóa thông báo', 'notifications', $id, "Đã xóa thông báo '{$notif['title']}'");
            set_flash('success', 'Đã xóa thông báo.');
        }

        $this->redirect('/notifications');
    }
}
