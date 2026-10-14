<?php

namespace App\Middleware;

use App\Helpers\Database;

class AuthMiddleware
{
    /**
     * Bắt buộc phải đăng nhập
     */
    public static function requireLogin(): void
    {
        if (!is_logged_in()) {
            set_flash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('/login');
        }
    }

    /**
     * Bắt buộc phải là quyền Quản trị viên (Admin)
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!is_admin()) {
            http_response_code(403);
            view('errors.403', ['message' => 'Bạn không có quyền truy cập trang quản trị này.'], 'none');
            exit;
        }
    }

    /**
     * Bắt buộc phải là quyền Người thuê (Tenant)
     */
    public static function requireTenant(): void
    {
        self::requireLogin();
        if (!is_tenant()) {
            set_flash('info', 'Chuyển hướng đến bảng điều khiển quản trị.');
            redirect('/dashboard');
        }
    }

    /**
     * Kiểm tra người thuê có quyền truy cập vào Hợp đồng này không
     */
    public static function checkTenantContractAccess(int $contractId): bool
    {
        if (is_admin()) {
            return true;
        }
        $tenantId = current_tenant_id();
        if (!$tenantId) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM contracts WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$contractId, $tenantId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Kiểm tra người thuê có quyền truy cập vào Hóa đơn này không
     */
    public static function checkTenantInvoiceAccess(int $invoiceId): bool
    {
        if (is_admin()) {
            return true;
        }
        $tenantId = current_tenant_id();
        if (!$tenantId) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM invoices WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$invoiceId, $tenantId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Kiểm tra người thuê có quyền truy cập vào Yêu cầu sửa chữa này không
     */
    public static function checkTenantMaintenanceAccess(int $requestId): bool
    {
        if (is_admin()) {
            return true;
        }
        $tenantId = current_tenant_id();
        if (!$tenantId) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM maintenance_requests WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$requestId, $tenantId]);
        return (bool)$stmt->fetch();
    }
}
