<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\RentalArea;
use Exception;

class ContractController extends Controller
{
    private Contract $contractModel;
    private Room $roomModel;
    private Tenant $tenantModel;
    private RentalArea $areaModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->contractModel = new Contract();
        $this->roomModel = new Room();
        $this->tenantModel = new Tenant();
        $this->areaModel = new RentalArea();
    }

    public function index(): void
    {
        $filters = [
            'status'  => $_GET['status'] ?? '',
            'area_id' => $_GET['area_id'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? '')
        ];

        $contracts = $this->contractModel->allWithRelations($filters);
        $areas = $this->areaModel->all('name ASC');

        $this->view('contracts.index', [
            'contracts' => $contracts,
            'areas'     => $areas,
            'filters'   => $filters
        ], 'admin');
    }

    public function create(): void
    {
        // Chỉ lấy phòng trống hoặc phòng đang chọn
        $rooms = $this->roomModel->allWithAreaAndTenant(['status' => 'empty']);
        $tenants = $this->tenantModel->all('fullname ASC');

        if (empty($rooms)) {
            set_flash('warning', 'Hiện tại không có phòng nào còn trống để lập hợp đồng mới. Vui lòng kiểm tra lại danh sách phòng.');
        }

        $this->view('contracts.create', [
            'rooms'   => $rooms,
            'tenants' => $tenants
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $contractCode = strtoupper(trim($_POST['contract_code'] ?? ''));
        $roomId = (int)($_POST['room_id'] ?? 0);
        $tenantId = (int)($_POST['tenant_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $rentPrice = (float)($_POST['rent_price'] ?? 0);
        $depositAmount = (float)($_POST['deposit_amount'] ?? 0);
        $paymentCycle = (int)($_POST['payment_cycle'] ?? 1);
        $terms = trim($_POST['terms'] ?? '');

        if (empty($contractCode) || !$roomId || !$tenantId || empty($startDate) || empty($endDate) || $rentPrice <= 0) {
            set_flash('danger', 'Vui lòng điền đầy đủ các thông tin bắt buộc của Hợp đồng.');
            $this->redirect('/contracts/create');
        }

        if (strtotime($endDate) <= strtotime($startDate)) {
            set_flash('danger', 'Ngày kết thúc hợp đồng phải sau ngày bắt đầu.');
            $this->redirect('/contracts/create');
        }

        try {
            $contractId = $this->contractModel->createContract([
                'contract_code'  => $contractCode,
                'room_id'        => $roomId,
                'tenant_id'      => $tenantId,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'rent_price'     => $rentPrice,
                'deposit_amount' => $depositAmount,
                'payment_cycle'  => $paymentCycle,
                'terms'          => $terms,
                'status'         => 'active'
            ]);

            log_activity('Tạo hợp đồng', 'contracts', $contractId, "Tạo hợp đồng {$contractCode}");
            set_flash('success', "Tạo hợp đồng {$contractCode} thành công! Phòng đã chuyển sang trạng thái Đang thuê.");
            $this->redirect('/contracts/show/' . $contractId);
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi tạo hợp đồng: ' . $e->getMessage());
            $this->redirect('/contracts/create');
        }
    }

    public function show(int|string $id): void
    {
        $id = (int)$id;
        $contract = $this->contractModel->findWithDetails($id);
        if (!$contract) {
            set_flash('danger', 'Không tìm thấy hợp đồng.');
            $this->redirect('/contracts');
        }

        $this->view('contracts.show', ['contract' => $contract], 'admin');
    }

    public function edit(int|string $id): void
    {
        $id = (int)$id;
        $contract = $this->contractModel->findWithDetails($id);
        if (!$contract) {
            set_flash('danger', 'Không tìm thấy hợp đồng.');
            $this->redirect('/contracts');
        }

        $this->view('contracts.edit', ['contract' => $contract], 'admin');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $contract = $this->contractModel->find($id);
        if (!$contract) {
            set_flash('danger', 'Không tìm thấy hợp đồng.');
            $this->redirect('/contracts');
        }

        $rentPrice = (float)($_POST['rent_price'] ?? 0);
        $depositAmount = (float)($_POST['deposit_amount'] ?? 0);
        $paymentCycle = (int)($_POST['payment_cycle'] ?? 1);
        $terms = trim($_POST['terms'] ?? '');

        if ($rentPrice <= 0) {
            set_flash('danger', 'Giá thuê phòng không hợp lệ.');
            $this->redirect('/contracts/edit/' . $id);
        }

        $this->contractModel->update($id, [
            'rent_price'     => $rentPrice,
            'deposit_amount' => $depositAmount,
            'payment_cycle'  => $paymentCycle,
            'terms'          => $terms
        ]);

        log_activity('Cập nhật hợp đồng', 'contracts', $id, "Cập nhật điều khoản hợp đồng {$contract['contract_code']}");
        set_flash('success', "Cập nhật hợp đồng thành công.");
        $this->redirect('/contracts/show/' . $id);
    }

    public function extend(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $newEndDate = $_POST['new_end_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');

        if (empty($newEndDate)) {
            set_flash('danger', 'Vui lòng chọn ngày kết thúc gia hạn mới.');
            $this->redirect('/contracts/show/' . $id);
        }

        try {
            $this->contractModel->extendContract($id, $newEndDate, $notes);
            log_activity('Gia hạn hợp đồng', 'contracts', $id, "Gia hạn hợp đồng đến ngày " . format_date($newEndDate));
            set_flash('success', 'Gia hạn hợp đồng thành công!');
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi gia hạn: ' . $e->getMessage());
        }

        $this->redirect('/contracts/show/' . $id);
    }

    public function terminate(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        try {
            $this->contractModel->terminateContract($id);
            log_activity('Chấm dứt hợp đồng', 'contracts', $id, "Thanh lý hợp đồng ID #{$id}");
            set_flash('success', 'Đã thanh lý và chấm dứt hợp đồng thành công! Phòng đã chuyển sang trạng thái Phòng Trống.');
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi thanh lý hợp đồng: ' . $e->getMessage());
        }

        $this->redirect('/contracts/show/' . $id);
    }

    public function printContract(int|string $id): void
    {
        $id = (int)$id;
        $contract = $this->contractModel->findWithDetails($id);
        if (!$contract) {
            set_flash('danger', 'Không tìm thấy hợp đồng.');
            $this->redirect('/contracts');
        }

        $this->view('contracts.print', ['contract' => $contract], 'none');
    }
}
