<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\MaintenanceRequest;
use App\Models\Expense;
use Exception;

class MaintenanceController extends Controller
{
    private MaintenanceRequest $maintenanceModel;
    private Expense $expenseModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->maintenanceModel = new MaintenanceRequest();
        $this->expenseModel = new Expense();
    }

    public function index(): void
    {
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? ''
        ];

        $requests = $this->maintenanceModel->allWithRelations($filters);
        $this->view('maintenance.index', [
            'requests' => $requests,
            'filters'  => $filters
        ], 'admin');
    }

    public function show(int|string $id): void
    {
        $id = (int)$id;
        $request = $this->maintenanceModel->findWithDetails($id);
        if (!$request) {
            set_flash('danger', 'Không tìm thấy yêu cầu sửa chữa.');
            $this->redirect('/maintenance');
        }

        $this->view('maintenance.show', ['request' => $request], 'admin');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $request = $this->maintenanceModel->find($id);
        if (!$request) {
            set_flash('danger', 'Không tìm thấy yêu cầu sửa chữa.');
            $this->redirect('/maintenance');
        }

        $status = $_POST['status'] ?? $request['status'];
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $cost = (float)($_POST['cost'] ?? 0);
        $recordExpense = !empty($_POST['record_expense']) && $cost > 0;

        $this->maintenanceModel->update($id, [
            'status'      => $status,
            'admin_notes' => $adminNotes,
            'cost'        => $cost
        ]);

        // Nếu hoàn thành và có chi phí, cho phép ghi tự động vào bảng chi phí vận hành
        if ($recordExpense && $status === 'completed') {
            try {
                $user = auth_user();
                $this->expenseModel->createExpense([
                    'title'        => "Chi phí sửa chữa: " . $request['title'],
                    'category'     => 'Bảo trì',
                    'amount'       => $cost,
                    'expense_date' => date('Y-m-d'),
                    'recipient'    => 'Thợ kỹ thuật / Nhà cung cấp',
                    'description'  => "Chi phí thực hiện cho yêu cầu {$request['request_code']}. Ghi chú: {$adminNotes}",
                    'created_by'   => $user['id'] ?? null
                ]);
            } catch (Exception $e) {
                // Tiếp tục
            }
        }

        log_activity('Cập nhật bảo trì', 'maintenance_requests', $id, "Cập nhật trạng thái '{$status}' cho yêu cầu {$request['request_code']}");
        set_flash('success', 'Cập nhật tiến độ xử lý yêu cầu thành công!');
        $this->redirect('/maintenance/show/' . $id);
    }
}
