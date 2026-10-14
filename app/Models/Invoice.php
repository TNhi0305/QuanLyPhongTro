<?php

namespace App\Models;

use App\Core\Model;
use Exception;
use PDO;

class Invoice extends Model
{
    protected string $table = 'invoices';

    /**
     * Lấy danh sách hóa đơn kèm quan hệ và bộ lọc
     */
    public function allWithRelations(array $filters = []): array
    {
        $sql = "
            SELECT i.*, 
                   c.contract_code,
                   r.room_code, 
                   r.room_number,
                   a.name AS area_name,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone
            FROM invoices i
            JOIN contracts c ON i.contract_id = c.id
            JOIN rooms r ON i.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON i.tenant_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['billing_month'])) {
            $sql .= " AND i.billing_month = ?";
            $params[] = $filters['billing_month'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['area_id'])) {
            $sql .= " AND r.area_id = ?";
            $params[] = $filters['area_id'];
        }

        if (!empty($filters['tenant_id'])) {
            $sql .= " AND i.tenant_id = ?";
            $params[] = $filters['tenant_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (i.invoice_code LIKE ? OR r.room_code LIKE ? OR t.fullname LIKE ? OR t.phone LIKE ?)";
            $kw = '%' . $filters['keyword'] . '%';
            $params = array_merge($params, [$kw, $kw, $kw, $kw]);
        }

        $sql .= " ORDER BY i.billing_month DESC, i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết đầy đủ của một hóa đơn bao gồm các khoản phí và lịch sử thanh toán
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "
            SELECT i.*, 
                   c.contract_code,
                   c.start_date AS contract_start,
                   c.end_date AS contract_end,
                   r.room_code, 
                   r.room_number,
                   a.name AS area_name,
                   a.address AS area_address,
                   a.contact_phone AS area_phone,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone,
                   t.email AS tenant_email,
                   t.id_card AS tenant_id_card,
                   m.electricity_old, m.electricity_new, m.electricity_usage,
                   m.water_old, m.water_new, m.water_usage
            FROM invoices i
            JOIN contracts c ON i.contract_id = c.id
            JOIN rooms r ON i.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON i.tenant_id = t.id
            LEFT JOIN meter_readings m ON i.meter_reading_id = m.id
            WHERE i.id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            return null;
        }

        // Lấy chi tiết các dòng mục
        $stmtDetails = $this->db->prepare("SELECT * FROM invoice_details WHERE invoice_id = ? ORDER BY id ASC");
        $stmtDetails->execute([$id]);
        $invoice['details'] = $stmtDetails->fetchAll();

        // Lấy lịch sử các đợt thanh toán
        $stmtPayments = $this->db->prepare("
            SELECT p.*, u.fullname AS created_by_name 
            FROM payments p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.invoice_id = ? 
            ORDER BY p.payment_date DESC, p.id DESC
        ");
        $stmtPayments->execute([$id]);
        $invoice['payments'] = $stmtPayments->fetchAll();

        return $invoice;
    }

    /**
     * Tạo mã hóa đơn duy nhất
     */
    public function generateInvoiceCode(string $billingMonth): string
    {
        $prefix = 'HDN-' . str_replace('-', '', $billingMonth) . '-';
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM invoices WHERE billing_month = ?");
        $stmt->execute([$billingMonth]);
        $seq = (int)$stmt->fetchColumn() + 1;
        return $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Tạo hóa đơn hoàn chỉnh kèm các khoản chi tiết
     */
    public function createInvoiceWithDetails(array $invoiceData, array $details): int
    {
        // Kiểm tra xem đã có hóa đơn cho hợp đồng này trong kỳ đó chưa
        $stmtCheck = $this->db->prepare("SELECT id FROM invoices WHERE contract_id = ? AND billing_month = ?");
        $stmtCheck->execute([$invoiceData['contract_id'], $invoiceData['billing_month']]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Hợp đồng này đã được lập hóa đơn cho kỳ " . $invoiceData['billing_month'] . ".");
        }

        if (empty($invoiceData['invoice_code'])) {
            $invoiceData['invoice_code'] = $this->generateInvoiceCode($invoiceData['billing_month']);
        }

        $invoiceData['paid_amount'] = 0.00;
        $invoiceData['remaining_amount'] = $invoiceData['total_amount'];
        $invoiceData['status'] = 'unpaid';

        $this->db->beginTransaction();
        try {
            $invoiceId = (int)$this->create($invoiceData);

            $stmtDetail = $this->db->prepare("
                INSERT INTO invoice_details (invoice_id, item_name, quantity, unit, unit_price, amount, note)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($details as $d) {
                $stmtDetail->execute([
                    $invoiceId,
                    $d['item_name'],
                    $d['quantity'],
                    $d['unit'],
                    $d['unit_price'],
                    $d['amount'],
                    $d['note'] ?? null
                ]);
            }

            $this->db->commit();
            return $invoiceId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Tự động sinh hóa đơn hàng loạt cho tất cả hợp đồng đang hiệu lực
     */
    public function autoGenerateInvoices(string $billingMonth, string $dueDate): array
    {
        $contractModel = new Contract();
        $activeContracts = $contractModel->allWithRelations(['status' => 'active']);

        $serviceModel = new Service();
        $elecRate = $serviceModel->getElectricityRate();
        $waterRate = $serviceModel->getWaterRate();
        $fixedServices = $serviceModel->getFixedServices();

        $meterModel = new MeterReading();

        $successCount = 0;
        $skippedCount = 0;

        foreach ($activeContracts as $c) {
            // Kiểm tra nếu đã có hóa đơn rồi thì bỏ qua
            $stmtCheck = $this->db->prepare("SELECT id FROM invoices WHERE contract_id = ? AND billing_month = ?");
            $stmtCheck->execute([$c['id'], $billingMonth]);
            if ($stmtCheck->fetch()) {
                $skippedCount++;
                continue;
            }

            // Tìm chỉ số điện nước tháng này nếu có
            $meter = $meterModel->findByRoomAndMonth((int)$c['room_id'], $billingMonth);
            $elecUsage = $meter ? (int)$meter['electricity_usage'] : 0;
            $waterUsage = $meter ? (int)$meter['water_usage'] : 0;

            $elecAmount = $elecUsage * $elecRate;
            $waterAmount = $waterUsage * $waterRate;

            $serviceAmount = 0;
            $details = [];

            // 1. Tiền phòng
            $details[] = [
                'item_name'  => 'Tiền thuê phòng ' . $c['room_code'] . ' (Kỳ ' . $billingMonth . ')',
                'quantity'   => 1,
                'unit'       => 'Tháng',
                'unit_price' => (float)$c['rent_price'],
                'amount'     => (float)$c['rent_price'],
                'note'       => 'Theo hợp đồng thuê phòng'
            ];

            // 2. Tiền điện
            if ($elecUsage > 0 || $meter) {
                $details[] = [
                    'item_name'  => 'Tiền điện (' . $elecUsage . ' kWh)',
                    'quantity'   => $elecUsage,
                    'unit'       => 'kWh',
                    'unit_price' => $elecRate,
                    'amount'     => $elecAmount,
                    'note'       => $meter ? "Số cũ: {$meter['electricity_old']}, Số mới: {$meter['electricity_new']}" : 'Chưa ghi số'
                ];
            }

            // 3. Tiền nước
            if ($waterUsage > 0 || $meter) {
                $details[] = [
                    'item_name'  => 'Tiền nước (' . $waterUsage . ' m³)',
                    'quantity'   => $waterUsage,
                    'unit'       => 'm³',
                    'unit_price' => $waterRate,
                    'amount'     => $waterAmount,
                    'note'       => $meter ? "Số cũ: {$meter['water_old']}, Số mới: {$meter['water_new']}" : 'Chưa ghi số'
                ];
            }

            // 4. Các dịch vụ cố định
            foreach ($fixedServices as $s) {
                $sPrice = (float)$s['price'];
                $serviceAmount += $sPrice;
                $details[] = [
                    'item_name'  => $s['name'],
                    'quantity'   => 1,
                    'unit'       => $s['unit'],
                    'unit_price' => $sPrice,
                    'amount'     => $sPrice,
                    'note'       => 'Phí dịch vụ cố định'
                ];
            }

            $totalAmount = (float)$c['rent_price'] + $elecAmount + $waterAmount + $serviceAmount;

            $invoiceData = [
                'contract_id'        => $c['id'],
                'room_id'            => $c['room_id'],
                'tenant_id'          => $c['tenant_id'],
                'meter_reading_id'   => $meter ? $meter['id'] : null,
                'billing_month'      => $billingMonth,
                'room_price'         => (float)$c['rent_price'],
                'electricity_amount' => $elecAmount,
                'water_amount'       => $waterAmount,
                'service_amount'     => $serviceAmount,
                'other_fees'         => 0.00,
                'discount'           => 0.00,
                'total_amount'       => $totalAmount,
                'due_date'           => $dueDate,
                'notes'              => 'Hóa đơn tiền phòng và dịch vụ kỳ ' . $billingMonth
            ];

            try {
                $this->createInvoiceWithDetails($invoiceData, $details);
                $successCount++;
            } catch (Exception $e) {
                // Tiếp tục xử lý các hợp đồng khác
            }
        }

        return ['success' => $successCount, 'skipped' => $skippedCount];
    }

    /**
     * Cập nhật trạng thái quá hạn cho các hóa đơn đã quá ngày due_date mà chưa thanh toán
     */
    public function updateOverdueStatuses(): void
    {
        $today = date('Y-m-d');
        $sql = "UPDATE invoices SET status = 'overdue' WHERE due_date < ? AND status IN ('unpaid', 'partially_paid') AND remaining_amount > 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
    }

    /**
     * Thống kê tổng quan cho Dashboard
     */
    public function getDashboardSummary(): array
    {
        $this->updateOverdueStatuses();
        $currentMonth = date('Y-m');

        // Thống kê phòng
        $roomStats = $this->db->query("
            SELECT 
                COUNT(*) AS total_rooms,
                SUM(CASE WHEN status = 'empty' THEN 1 ELSE 0 END) AS empty_rooms,
                SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) AS rented_rooms,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_rooms
            FROM rooms
        ")->fetch();

        // Tổng số khu trọ
        $totalAreas = (int)$this->db->query("SELECT COUNT(*) FROM rental_areas")->fetchColumn();

        // Tổng số người thuê
        $totalTenants = (int)$this->db->query("SELECT COUNT(*) FROM tenants")->fetchColumn();

        // Doanh thu tháng này
        $stmtMonth = $this->db->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS month_total_invoiced,
                COALESCE(SUM(paid_amount), 0) AS month_paid,
                COALESCE(SUM(remaining_amount), 0) AS month_debt
            FROM invoices 
            WHERE billing_month = ?
        ");
        $stmtMonth->execute([$currentMonth]);
        $monthStats = $stmtMonth->fetch();

        // Tổng toàn bộ tiền đã thu và tổng công nợ
        $allStats = $this->db->query("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS total_invoiced,
                COALESCE(SUM(paid_amount), 0) AS total_collected,
                COALESCE(SUM(remaining_amount), 0) AS total_debt
            FROM invoices
        ")->fetch();

        return [
            'total_areas'       => $totalAreas,
            'total_rooms'       => (int)($roomStats['total_rooms'] ?? 0),
            'empty_rooms'       => (int)($roomStats['empty_rooms'] ?? 0),
            'rented_rooms'      => (int)($roomStats['rented_rooms'] ?? 0),
            'maintenance_rooms' => (int)($roomStats['maintenance_rooms'] ?? 0),
            'total_tenants'     => $totalTenants,
            'month_revenue'     => (float)($monthStats['month_total_invoiced'] ?? 0),
            'month_paid'        => (float)($monthStats['month_paid'] ?? 0),
            'month_debt'        => (float)($monthStats['month_debt'] ?? 0),
            'total_collected'   => (float)($allStats['total_collected'] ?? 0),
            'total_debt'        => (float)($allStats['total_debt'] ?? 0)
        ];
    }

    /**
     * Lấy danh sách hóa đơn sắp đến hạn (trong vòng 5 ngày tới)
     */
    public function getUpcomingDue(int $limit = 5): array
    {
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime('+5 days'));
        $sql = "
            SELECT i.*, r.room_code, t.fullname AS tenant_name, t.phone AS tenant_phone
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            JOIN tenants t ON i.tenant_id = t.id
            WHERE i.due_date BETWEEN ? AND ? AND i.remaining_amount > 0 AND i.status != 'paid'
            ORDER BY i.due_date ASC
            LIMIT {$limit}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today, $future]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách hóa đơn quá hạn
     */
    public function getOverdue(int $limit = 5): array
    {
        $sql = "
            SELECT i.*, r.room_code, t.fullname AS tenant_name, t.phone AS tenant_phone
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            JOIN tenants t ON i.tenant_id = t.id
            WHERE (i.status = 'overdue' OR (i.due_date < CURDATE() AND i.remaining_amount > 0))
            ORDER BY i.due_date ASC
            LIMIT {$limit}
        ";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Lấy dữ liệu biểu đồ doanh thu theo 6 tháng gần nhất
     */
    public function getRevenueChartData(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i months"));
        }

        $invoicedData = [];
        $collectedData = [];
        $expenseData = [];

        foreach ($months as $m) {
            // Hóa đơn
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0), COALESCE(SUM(paid_amount), 0) FROM invoices WHERE billing_month = ?");
            $stmt->execute([$m]);
            [$inv, $paid] = $stmt->fetch(PDO::FETCH_NUM);
            $invoicedData[] = (float)$inv;
            $collectedData[] = (float)$paid;

            // Chi phí cùng tháng
            $stmtExp = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = ?");
            $stmtExp->execute([$m]);
            $expenseData[] = (float)$stmtExp->fetchColumn();
        }

        return [
            'labels'    => array_map(function($m) { return 'T' . date('n/Y', strtotime($m . '-01')); }, $months),
            'invoiced'  => $invoicedData,
            'collected' => $collectedData,
            'expenses'  => $expenseData
        ];
    }
}
