<?php

namespace App\Models;

use App\Core\Model;
use Exception;
use PDO;

class Payment extends Model
{
    protected string $table = 'payments';

    /**
     * Lấy danh sách phiếu thanh toán kèm thông tin hóa đơn và người lập
     */
    public function allWithRelations(?int $invoiceId = null): array
    {
        $sql = "
            SELECT p.*, 
                   i.invoice_code, 
                   i.billing_month,
                   r.room_code,
                   t.fullname AS tenant_name,
                   u.fullname AS created_by_name
            FROM payments p
            JOIN invoices i ON p.invoice_id = i.id
            JOIN rooms r ON i.room_id = r.id
            JOIN tenants t ON i.tenant_id = t.id
            LEFT JOIN users u ON p.created_by = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($invoiceId !== null) {
            $sql .= " AND p.invoice_id = ?";
            $params[] = $invoiceId;
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ghi nhận thanh toán mới cho hóa đơn
     */
    public function recordPayment(array $data): int
    {
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($data['invoice_id']);

        if (!$invoice) {
            throw new Exception("Hóa đơn không tồn tại.");
        }

        $amount = (float)$data['amount'];
        if ($amount <= 0) {
            throw new Exception("Số tiền thanh toán phải lớn hơn 0 VNĐ.");
        }

        $remaining = (float)$invoice['remaining_amount'];
        if ($amount > $remaining) {
            throw new Exception("Số tiền thanh toán (" . format_currency($amount) . ") không được vượt quá số tiền còn nợ (" . format_currency($remaining) . ").");
        }

        if (empty($data['payment_code'])) {
            $prefix = 'TT-' . date('Ym') . '-';
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM payments WHERE DATE_FORMAT(created_at, '%Y%m') = ?");
            $stmtCount->execute([date('Ym')]);
            $seq = (int)$stmtCount->fetchColumn() + 1;
            $data['payment_code'] = $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
        }

        $this->db->beginTransaction();
        try {
            $paymentId = (int)$this->create($data);

            // Cập nhật hóa đơn
            $newPaid = (float)$invoice['paid_amount'] + $amount;
            $newRemaining = (float)$invoice['total_amount'] - $newPaid;
            $newStatus = ($newRemaining <= 0) ? 'paid' : 'partially_paid';

            $invoiceModel->update($invoice['id'], [
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemaining,
                'status'           => $newStatus
            ]);

            $this->db->commit();
            return $paymentId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
