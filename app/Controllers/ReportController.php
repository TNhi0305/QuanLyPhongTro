<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Invoice;
use App\Models\RentalArea;
use App\Helpers\Database;

class ReportController extends Controller
{
    private Invoice $invoiceModel;
    private RentalArea $areaModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->invoiceModel = new Invoice();
        $this->areaModel = new RentalArea();
    }

    public function index(): void
    {
        $pdo = Database::getConnection();

        $selectedMonth = $_GET['month'] ?? date('Y-m');
        $selectedArea = !empty($_GET['area_id']) ? (int)$_GET['area_id'] : null;

        // 1. Doanh thu trong kỳ
        $sqlRev = "
            SELECT 
                COALESCE(SUM(i.total_amount), 0) AS total_invoiced,
                COALESCE(SUM(i.paid_amount), 0) AS total_collected,
                COALESCE(SUM(i.remaining_amount), 0) AS total_debt,
                COUNT(i.id) AS invoice_count
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            WHERE 1=1
        ";
        $paramsRev = [];
        if (!empty($selectedMonth)) {
            $sqlRev .= " AND i.billing_month = ?";
            $paramsRev[] = $selectedMonth;
        }
        if (!empty($selectedArea)) {
            $sqlRev .= " AND r.area_id = ?";
            $paramsRev[] = $selectedArea;
        }

        $stmtRev = $pdo->prepare($sqlRev);
        $stmtRev->execute($paramsRev);
        $revenueStats = $stmtRev->fetch();

        // 2. Chi phí trong kỳ
        $sqlExp = "SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE 1=1";
        $paramsExp = [];
        if (!empty($selectedMonth)) {
            $sqlExp .= " AND DATE_FORMAT(expense_date, '%Y-%m') = ?";
            $paramsExp[] = $selectedMonth;
        }
        $stmtExp = $pdo->prepare($sqlExp);
        $stmtExp->execute($paramsExp);
        $totalExpenses = (float)$stmtExp->fetchColumn();

        // Lợi nhuận ròng = Tiền thực thu - Chi phí
        $netProfit = (float)$revenueStats['total_collected'] - $totalExpenses;

        // 3. Thống kê tình trạng phòng
        $sqlRooms = "
            SELECT 
                COUNT(r.id) AS total_rooms,
                SUM(CASE WHEN r.status = 'empty' THEN 1 ELSE 0 END) AS empty_rooms,
                SUM(CASE WHEN r.status = 'rented' THEN 1 ELSE 0 END) AS rented_rooms,
                SUM(CASE WHEN r.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_rooms
            FROM rooms r
            WHERE 1=1
        ";
        $paramsRooms = [];
        if (!empty($selectedArea)) {
            $sqlRooms .= " AND r.area_id = ?";
            $paramsRooms[] = $selectedArea;
        }
        $stmtRooms = $pdo->prepare($sqlRooms);
        $stmtRooms->execute($paramsRooms);
        $roomStats = $stmtRooms->fetch();

        // 4. Danh sách hóa đơn chi tiết theo kỳ lọc
        $sqlList = "
            SELECT i.*, r.room_code, r.room_number, a.name AS area_name, t.fullname AS tenant_name, t.phone AS tenant_phone
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON i.tenant_id = t.id
            WHERE 1=1
        ";
        $paramsList = [];
        if (!empty($selectedMonth)) {
            $sqlList .= " AND i.billing_month = ?";
            $paramsList[] = $selectedMonth;
        }
        if (!empty($selectedArea)) {
            $sqlList .= " AND r.area_id = ?";
            $paramsList[] = $selectedArea;
        }
        $sqlList .= " ORDER BY r.room_code ASC";
        $stmtList = $pdo->prepare($sqlList);
        $stmtList->execute($paramsList);
        $invoiceList = $stmtList->fetchAll();

        $areas = $this->areaModel->all('name ASC');

        $this->view('reports.index', [
            'revenueStats'  => $revenueStats,
            'totalExpenses' => $totalExpenses,
            'netProfit'     => $netProfit,
            'roomStats'     => $roomStats,
            'invoiceList'   => $invoiceList,
            'areas'         => $areas,
            'selectedMonth' => $selectedMonth,
            'selectedArea'  => $selectedArea
        ], 'admin');
    }
}
