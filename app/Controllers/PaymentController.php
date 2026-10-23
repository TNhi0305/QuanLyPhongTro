<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Payment;
use App\Models\Invoice;
use Exception;

class PaymentController extends Controller
{
    private Payment $paymentModel;
    private Invoice $invoiceModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->paymentModel = new Payment();
        $this->invoiceModel = new Invoice();
    }

    public function index(): void
    {
        $payments = $this->paymentModel->allWithRelations();
        $this->view('payments.index', ['payments' => $payments], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentDate = $_POST['payment_date'] ?? date('Y-m-d H:i:s');
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $transactionRef = trim($_POST['transaction_ref'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$invoiceId || $amount <= 0) {
            set_flash('danger', 'Vui lòng chọn hóa đơn và nhập số tiền thanh toán hợp lệ (> 0 VNĐ).');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/invoices');
        }

        try {
            $user = auth_user();
            $paymentId = $this->paymentModel->recordPayment([
                'invoice_id'      => $invoiceId,
                'payment_date'    => $paymentDate,
                'amount'          => $amount,
                'payment_method'  => $paymentMethod,
                'transaction_ref' => $transactionRef,
                'notes'           => $notes,
                'created_by'      => $user['id'] ?? null
            ]);

            log_activity('Thu tiền phòng', 'payments', $paymentId, "Thu tiền hóa đơn ID #{$invoiceId}: " . format_currency($amount));

            // Phát sinh thông báo Push Notification tới người thuê (Sự kiện nghiệp vụ 2)
            $inv = $this->invoiceModel->find($invoiceId);
            if ($inv && !empty($inv['tenant_id'])) {
                $statusText = ($inv['status'] === 'paid') ? 'Đã thanh toán đủ.' : 'Thanh toán một phần.';
                \App\Helpers\PushNotification::createAndSend(
                    'Xác nhận thanh toán tiền phòng',
                    "Chủ trọ đã xác nhận thanh toán " . format_currency($amount) . " cho hóa đơn " . ($inv['invoice_code'] ?? "#{$invoiceId}") . ". Trạng thái: {$statusText}",
                    'payment',
                    (int)$inv['tenant_id'],
                    null,
                    'invoice',
                    $invoiceId,
                    [
                        'invoice_id' => $invoiceId,
                        'paid_amount' => $amount,
                        'status' => $inv['status'] ?? 'paid',
                        'type' => 'payment_received'
                    ],
                    $user['id'] ?? null
                );
            }

            set_flash('success', "Ghi nhận thanh toán " . format_currency($amount) . " thành công!");
            $this->redirect('/invoices/show/' . $invoiceId);
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi thanh toán: ' . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/invoices/show/' . $invoiceId);
        }
    }
}
