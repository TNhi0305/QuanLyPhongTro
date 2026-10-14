<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Service;
use App\Models\MeterReading;
use App\Models\RentalArea;
use Exception;

class InvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private Contract $contractModel;
    private Service $serviceModel;
    private MeterReading $meterModel;
    private RentalArea $areaModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->invoiceModel = new Invoice();
        $this->contractModel = new Contract();
        $this->serviceModel = new Service();
        $this->meterModel = new MeterReading();
        $this->areaModel = new RentalArea();
    }

    public function index(): void
    {
        $this->invoiceModel->updateOverdueStatuses();

        $filters = [
            'billing_month' => $_GET['billing_month'] ?? '',
            'status'        => $_GET['status'] ?? '',
            'area_id'       => $_GET['area_id'] ?? '',
            'keyword'       => trim($_GET['keyword'] ?? '')
        ];

        $invoices = $this->invoiceModel->allWithRelations($filters);
        $areas = $this->areaModel->all('name ASC');

        $this->view('invoices.index', [
            'invoices' => $invoices,
            'areas'    => $areas,
            'filters'  => $filters
        ], 'admin');
    }

    public function create(): void
    {
        $activeContracts = $this->contractModel->allWithRelations(['status' => 'active']);
        $services = $this->serviceModel->allActive();
        $elecRate = $this->serviceModel->getElectricityRate();
        $waterRate = $this->serviceModel->getWaterRate();

        $this->view('invoices.create', [
            'contracts' => $activeContracts,
            'services'  => $services,
            'elecRate'  => $elecRate,
            'waterRate' => $waterRate
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $contractId = (int)($_POST['contract_id'] ?? 0);
        $billingMonth = trim($_POST['billing_month'] ?? date('Y-m'));
        $dueDate = $_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $notes = trim($_POST['notes'] ?? '');

        if (!$contractId || empty($billingMonth) || empty($dueDate)) {
            set_flash('danger', 'Vui lòng chọn Hợp đồng, Kỳ tính tiền và Hạn thanh toán.');
            $this->redirect('/invoices/create');
        }

        $contract = $this->contractModel->findWithDetails($contractId);
        if (!$contract) {
            set_flash('danger', 'Hợp đồng không tồn tại.');
            $this->redirect('/invoices/create');
        }

        $roomPrice = (float)($_POST['room_price'] ?? $contract['rent_price']);
        $elecUsage = (float)($_POST['electricity_usage'] ?? 0);
        $elecRate = (float)($_POST['electricity_rate'] ?? 3500);
        $elecAmount = $elecUsage * $elecRate;

        $waterUsage = (float)($_POST['water_usage'] ?? 0);
        $waterRate = (float)($_POST['water_rate'] ?? 20000);
        $waterAmount = $waterUsage * $waterRate;

        $serviceAmount = (float)($_POST['service_amount'] ?? 0);
        $otherFees = (float)($_POST['other_fees'] ?? 0);
        $discount = (float)($_POST['discount'] ?? 0);

        $totalAmount = $roomPrice + $elecAmount + $waterAmount + $serviceAmount + $otherFees - $discount;

        if ($totalAmount < 0) {
            $totalAmount = 0;
        }

        // Tạo danh sách line items
        $details = [];
        $details[] = [
            'item_name'  => 'Tiền thuê phòng ' . $contract['room_code'] . ' (Kỳ ' . $billingMonth . ')',
            'quantity'   => 1,
            'unit'       => 'Tháng',
            'unit_price' => $roomPrice,
            'amount'     => $roomPrice,
            'note'       => 'Tiền phòng theo thỏa thuận'
        ];

        if ($elecUsage > 0) {
            $details[] = [
                'item_name'  => "Tiền điện ({$elecUsage} kWh)",
                'quantity'   => $elecUsage,
                'unit'       => 'kWh',
                'unit_price' => $elecRate,
                'amount'     => $elecAmount,
                'note'       => "Đơn giá: " . format_currency($elecRate) . "/kWh"
            ];
        }

        if ($waterUsage > 0) {
            $details[] = [
                'item_name'  => "Tiền nước ({$waterUsage} m³)",
                'quantity'   => $waterUsage,
                'unit'       => 'm³',
                'unit_price' => $waterRate,
                'amount'     => $waterAmount,
                'note'       => "Đơn giá: " . format_currency($waterRate) . "/m³"
            ];
        }

        if (!empty($_POST['service_items']) && is_array($_POST['service_items'])) {
            foreach ($_POST['service_items'] as $sItem) {
                if (!empty($sItem['name']) && !empty($sItem['amount'])) {
                    $details[] = [
                        'item_name'  => $sItem['name'],
                        'quantity'   => 1,
                        'unit'       => 'Tháng',
                        'unit_price' => (float)$sItem['amount'],
                        'amount'     => (float)$sItem['amount'],
                        'note'       => 'Phí dịch vụ'
                    ];
                }
            }
        }

        if ($otherFees > 0) {
            $details[] = [
                'item_name'  => 'Chi phí khác',
                'quantity'   => 1,
                'unit'       => 'Khoản',
                'unit_price' => $otherFees,
                'amount'     => $otherFees,
                'note'       => $_POST['other_fees_note'] ?? ''
            ];
        }

        if ($discount > 0) {
            $details[] = [
                'item_name'  => 'Giảm giá / Ưu đãi',
                'quantity'   => 1,
                'unit'       => 'Khoản',
                'unit_price' => -$discount,
                'amount'     => -$discount,
                'note'       => $_POST['discount_note'] ?? ''
            ];
        }

        // Tìm chỉ số điện nước nếu có
        $meter = $this->meterModel->findByRoomAndMonth((int)$contract['room_id'], $billingMonth);

        $invoiceData = [
            'contract_id'        => $contractId,
            'room_id'            => $contract['room_id'],
            'tenant_id'          => $contract['tenant_id'],
            'meter_reading_id'   => $meter ? $meter['id'] : null,
            'billing_month'      => $billingMonth,
            'room_price'         => $roomPrice,
            'electricity_amount' => $elecAmount,
            'water_amount'       => $waterAmount,
            'service_amount'     => $serviceAmount,
            'other_fees'         => $otherFees,
            'discount'           => $discount,
            'total_amount'       => $totalAmount,
            'due_date'           => $dueDate,
            'notes'              => $notes
        ];

        try {
            $invId = $this->invoiceModel->createInvoiceWithDetails($invoiceData, $details);
            log_activity('Lập hóa đơn', 'invoices', $invId, "Lập hóa đơn tiền phòng kỳ {$billingMonth}");
            set_flash('success', "Lập hóa đơn thành công!");
            $this->redirect('/invoices/show/' . $invId);
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/invoices/create');
        }
    }

    /**
     * Tự động phát hành hóa đơn hàng loạt
     */
    public function autoGenerate(): void
    {
        $this->validateCsrf();

        $billingMonth = trim($_POST['billing_month'] ?? date('Y-m'));
        $dueDate = trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+10 days')));

        if (empty($billingMonth) || empty($dueDate)) {
            set_flash('danger', 'Vui lòng chọn kỳ tính tiền và hạn thanh toán.');
            $this->redirect('/invoices');
        }

        $result = $this->invoiceModel->autoGenerateInvoices($billingMonth, $dueDate);
        log_activity('Tự động tạo hóa đơn', 'invoices', null, "Sinh tự động {$result['success']} hóa đơn kỳ {$billingMonth}");

        set_flash('success', "Đã phát sinh thành công {$result['success']} hóa đơn cho kỳ {$billingMonth} (Bỏ qua {$result['skipped']} phòng đã có hóa đơn).");
        $this->redirect('/invoices?billing_month=' . $billingMonth);
    }

    public function show(int|string $id): void
    {
        $id = (int)$id;
        $invoice = $this->invoiceModel->findWithDetails($id);
        if (!$invoice) {
            set_flash('danger', 'Không tìm thấy hóa đơn.');
            $this->redirect('/invoices');
        }

        $this->view('invoices.show', ['invoice' => $invoice], 'admin');
    }

    public function printInvoice(int|string $id): void
    {
        $id = (int)$id;
        $invoice = $this->invoiceModel->findWithDetails($id);
        if (!$invoice) {
            set_flash('danger', 'Không tìm thấy hóa đơn.');
            $this->redirect('/invoices');
        }

        $this->view('invoices.print', ['invoice' => $invoice], 'none');
    }
}
