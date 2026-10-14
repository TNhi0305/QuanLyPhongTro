<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Tenant;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\MeterReading;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\User;
use Exception;

class TenantPortalController extends Controller
{
    private Tenant $tenantModel;
    private Contract $contractModel;
    private Room $roomModel;
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private MeterReading $meterModel;
    private MaintenanceRequest $maintenanceModel;
    private Notification $notifModel;
    private User $userModel;

    public function __construct()
    {
        AuthMiddleware::requireTenant();
        $this->tenantModel = new Tenant();
        $this->contractModel = new Contract();
        $this->roomModel = new Room();
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->meterModel = new MeterReading();
        $this->maintenanceModel = new MaintenanceRequest();
        $this->notifModel = new Notification();
        $this->userModel = new User();
    }

    private function getTenantOrDie(): array
    {
        $tenantId = current_tenant_id();
        if (!$tenantId) {
            set_flash('danger', 'Tài khoản của bạn chưa được liên kết với hồ sơ người thuê phòng. Vui lòng liên hệ chủ trọ.');
            $this->redirect('/login');
        }
        $tenant = $this->tenantModel->findWithDetails($tenantId);
        if (!$tenant) {
            set_flash('danger', 'Không tìm thấy hồ sơ người thuê.');
            $this->redirect('/login');
        }
        return $tenant;
    }

    public function dashboard(): void
    {
        $tenant = $this->getTenantOrDie();
        $activeContract = $this->contractModel->getActiveContractByTenant($tenant['id']);

        $currentInvoice = null;
        if ($activeContract) {
            $currentMonth = date('Y-m');
            $invoices = $this->invoiceModel->allWithRelations([
                'tenant_id'     => $tenant['id'],
                'billing_month' => $currentMonth
            ]);
            $currentInvoice = $invoices[0] ?? null;
        }

        $notifications = $this->notifModel->allForTenant($tenant['id']);
        $recentRequests = $this->maintenanceModel->allWithRelations(['tenant_id' => $tenant['id']]);

        $this->view('tenant_portal.dashboard', [
            'tenant'         => $tenant,
            'contract'       => $activeContract,
            'currentInvoice' => $currentInvoice,
            'notifications'  => array_slice($notifications, 0, 5),
            'recentRequests' => array_slice($recentRequests, 0, 5)
        ], 'tenant');
    }

    public function room(): void
    {
        $tenant = $this->getTenantOrDie();
        $activeContract = $this->contractModel->getActiveContractByTenant($tenant['id']);

        $room = null;
        if ($activeContract) {
            $room = $this->roomModel->findWithDetails((int)$activeContract['room_id']);
        }

        $this->view('tenant_portal.room', [
            'tenant'   => $tenant,
            'contract' => $activeContract,
            'room'     => $room
        ], 'tenant');
    }

    public function contract(): void
    {
        $tenant = $this->getTenantOrDie();
        $activeContract = $this->contractModel->getActiveContractByTenant($tenant['id']);

        $this->view('tenant_portal.contract', [
            'tenant'   => $tenant,
            'contract' => $activeContract
        ], 'tenant');
    }

    public function invoices(): void
    {
        $tenant = $this->getTenantOrDie();
        $this->invoiceModel->updateOverdueStatuses();

        $invoices = $this->invoiceModel->allWithRelations(['tenant_id' => $tenant['id']]);

        $this->view('tenant_portal.invoices', [
            'tenant'   => $tenant,
            'invoices' => $invoices
        ], 'tenant');
    }

    public function invoiceDetail(int|string $id): void
    {
        $id = (int)$id;
        $tenant = $this->getTenantOrDie();

        // BẢO MẬT BACKEND: Ngăn chặn người thuê xem hóa đơn của người khác bằng cách sửa URL!
        if (!AuthMiddleware::checkTenantInvoiceAccess($id)) {
            http_response_code(403);
            set_flash('danger', 'Bạn không có quyền xem hóa đơn này.');
            $this->redirect('/tenant/invoices');
        }

        $invoice = $this->invoiceModel->findWithDetails($id);
        if (!$invoice) {
            set_flash('danger', 'Hóa đơn không tồn tại.');
            $this->redirect('/tenant/invoices');
        }

        $this->view('tenant_portal.invoice_detail', [
            'tenant'  => $tenant,
            'invoice' => $invoice
        ], 'tenant');
    }

    public function meters(): void
    {
        $tenant = $this->getTenantOrDie();
        $activeContract = $this->contractModel->getActiveContractByTenant($tenant['id']);

        $readings = [];
        if ($activeContract) {
            $readings = $this->meterModel->allWithRooms(null, null);
            // Lọc chỉ lấy cho phòng của mình
            $readings = array_filter($readings, function($r) use ($activeContract) {
                return (int)$r['room_id'] === (int)$activeContract['room_id'];
            });
        }

        $this->view('tenant_portal.meters', [
            'tenant'   => $tenant,
            'contract' => $activeContract,
            'readings' => $readings
        ], 'tenant');
    }

    public function payments(): void
    {
        $tenant = $this->getTenantOrDie();
        $allPayments = $this->paymentModel->allWithRelations();

        // Lọc chỉ lấy các thanh toán thuộc về hóa đơn của người thuê này
        $myPayments = array_filter($allPayments, function($p) use ($tenant) {
            return $p['tenant_name'] === $tenant['fullname'];
        });

        $this->view('tenant_portal.payments', [
            'tenant'   => $tenant,
            'payments' => $myPayments
        ], 'tenant');
    }

    public function maintenance(): void
    {
        $tenant = $this->getTenantOrDie();
        $requests = $this->maintenanceModel->allWithRelations(['tenant_id' => $tenant['id']]);

        $this->view('tenant_portal.maintenance', [
            'tenant'   => $tenant,
            'requests' => $requests
        ], 'tenant');
    }

    public function createMaintenance(): void
    {
        $tenant = $this->getTenantOrDie();
        $activeContract = $this->contractModel->getActiveContractByTenant($tenant['id']);

        if (!$activeContract) {
            set_flash('warning', 'Bạn chưa có hợp đồng thuê phòng nào đang hoạt động để gửi yêu cầu bảo trì.');
            $this->redirect('/tenant/maintenance');
        }

        $this->view('tenant_portal.maintenance_create', [
            'tenant'   => $tenant,
            'contract' => $activeContract
        ], 'tenant');
    }

    public function storeMaintenance(): void
    {
        $this->validateCsrf();
        $tenant = $this->getTenantOrDie();
        $activeContract = $this->contractModel->getActiveContractByTenant($tenant['id']);

        if (!$activeContract) {
            set_flash('danger', 'Không tìm thấy hợp đồng thuê phòng.');
            $this->redirect('/tenant/maintenance');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';

        if (empty($title) || empty($description)) {
            set_flash('danger', 'Vui lòng nhập tiêu đề và mô tả sự cố cần sửa chữa.');
            $this->redirect('/tenant/maintenance/create');
        }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $imagePath = upload_image($_FILES['image'], 'maintenance');
            } catch (Exception $e) {
                set_flash('danger', 'Lỗi tải ảnh: ' . $e->getMessage());
                $this->redirect('/tenant/maintenance/create');
            }
        }

        try {
            $reqId = $this->maintenanceModel->createRequest([
                'room_id'     => $activeContract['room_id'],
                'tenant_id'   => $tenant['id'],
                'title'       => $title,
                'description' => $description,
                'priority'    => $priority,
                'image_path'  => $imagePath,
                'status'      => 'pending'
            ]);

            log_activity('Gửi yêu cầu sửa chữa', 'maintenance_requests', $reqId, "Người thuê {$tenant['fullname']} gửi yêu cầu {$title}");
            set_flash('success', 'Yêu cầu sửa chữa đã được gửi tới chủ trọ thành công! Ban quản lý sẽ liên hệ xử lý sớm nhất.');
            $this->redirect('/tenant/maintenance');
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/tenant/maintenance/create');
        }
    }

    public function notifications(): void
    {
        $tenant = $this->getTenantOrDie();
        $notifications = $this->notifModel->allForTenant($tenant['id']);

        $this->view('tenant_portal.notifications', [
            'tenant'        => $tenant,
            'notifications' => $notifications
        ], 'tenant');
    }
}
