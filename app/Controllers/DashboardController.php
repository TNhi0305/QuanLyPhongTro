<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    private Invoice $invoiceModel;
    private MaintenanceRequest $maintenanceModel;
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->maintenanceModel = new MaintenanceRequest();
        $this->logModel = new ActivityLog();
    }

    public function index(): void
    {
        AuthMiddleware::requireAdmin();

        $stats = $this->invoiceModel->getDashboardSummary();
        $upcomingInvoices = $this->invoiceModel->getUpcomingDue(5);
        $overdueInvoices = $this->invoiceModel->getOverdue(5);
        $recentMaintenance = $this->maintenanceModel->allWithRelations(['status' => 'pending']);
        $chartData = $this->invoiceModel->getRevenueChartData();
        $recentLogs = $this->logModel->getRecent(10);

        $this->view('dashboard.index', [
            'stats'             => $stats,
            'upcomingInvoices'  => $upcomingInvoices,
            'overdueInvoices'   => $overdueInvoices,
            'recentMaintenance' => $recentMaintenance,
            'chartData'         => $chartData,
            'recentLogs'        => $recentLogs
        ], 'admin');
    }
}
