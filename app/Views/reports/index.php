<?php

/**
 * @var array $areas
 * @var string $selectedMonth
 * @var string $selectedArea
 * @var array $revenueStats
 * @var float $netProfit
 * @var float $totalExpenses
 * @var array $roomStats
 * @var array $debtStats
 */
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Báo Cáo Doanh Thu & Tài Chính</h4>
        <span class="text-muted small">Thống kê chi tiết doanh thu, chi phí, lợi nhuận ròng và công nợ theo thời gian</span>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer"></i> In Báo Cáo
    </button>
</div>

<!-- Bộ lọc kỳ báo cáo -->
<div class="card mb-4 shadow-sm no-print">
    <div class="card-body p-3">
        <form action="<?= url('/reports') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1">Kỳ Tính Tiền (Tháng/Năm)</label>
                <input type="month" class="form-control" name="month" value="<?= e($selectedMonth) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1">Khu Trọ / Dãy Trọ</label>
                <select name="area_id" class="form-select">
                    <option value="">-- Tất cả khu trọ --</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $selectedArea == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Lọc Báo Cáo</button>
            </div>
        </form>
    </div>
</div>

<!-- 4 Thẻ chỉ số tài chính -->
<div class="row g-3 mb-4">
    <!-- Doanh thu phát hành -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">TỔNG HÓA ĐƠN</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1"><?= format_currency($revenueStats['total_invoiced'] ?? 0) ?></h4>
                    <span class="text-muted small"><?= (int)$revenueStats['invoice_count'] ?> hóa đơn phát hành</span>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Thực thu -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">TIỀN THỰC THU</span>
                    <h4 class="fw-bold text-success mb-0 mt-1"><?= format_currency($revenueStats['total_collected'] ?? 0) ?></h4>
                    <span class="text-success small fw-medium">
                        Tỷ lệ thu: <?= ($revenueStats['total_invoiced'] > 0) ? round(($revenueStats['total_collected'] / $revenueStats['total_invoiced']) * 100) : 0 ?>%
                    </span>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Chi phí vận hành -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">CHI PHÍ VẬN HÀNH</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1"><?= format_currency($totalExpenses) ?></h4>
                    <span class="text-muted small">Bảo trì, vệ sinh, vật tư</span>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Lợi nhuận ròng -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3 bg-primary bg-opacity-10 border-primary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">LỢI NHUẬN RÒNG</span>
                    <h4 class="fw-bold <?= $netProfit >= 0 ? 'text-primary' : 'text-danger' ?> mb-0 mt-1"><?= format_currency($netProfit) ?></h4>
                    <span class="text-muted small">Thực thu - Chi phí</span>
                </div>
                <div class="stat-icon bg-primary text-white">
                    <i class="bi bi-piggy-bank"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tình hình phòng & Công nợ -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-pie-chart text-primary me-2"></i>Tình Trạng Lấp Đầy Phòng
            </div>
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-3">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted small">Tổng số</span>
                            <div class="fw-bold fs-5 text-dark"><?= (int)$roomStats['total_rooms'] ?></div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded bg-success bg-opacity-10 border-success">
                            <span class="text-success small fw-bold">Đang thuê</span>
                            <div class="fw-bold fs-5 text-success"><?= (int)$roomStats['rented_rooms'] ?></div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded bg-secondary bg-opacity-10">
                            <span class="text-secondary small fw-bold">Còn trống</span>
                            <div class="fw-bold fs-5 text-secondary"><?= (int)$roomStats['empty_rooms'] ?></div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded bg-warning bg-opacity-10 border-warning">
                            <span class="text-warning text-dark small fw-bold">Bảo trì</span>
                            <div class="fw-bold fs-5 text-dark"><?= (int)$roomStats['maintenance_rooms'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold text-danger">
                <i class="bi bi-exclamation-triangle text-danger me-2"></i>Công Nợ Còn Tồn Đọng
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Tổng số tiền chưa thu:</span>
                    <span class="fw-bold fs-4 text-danger"><?= format_currency($revenueStats['total_debt'] ?? 0) ?></span>
                </div>
                <div class="progress" style="height: 10px;">
                    <?php
                    $totalInv = (float)($revenueStats['total_invoiced'] ?? 0);
                    $paidPercent = $totalInv > 0 ? round(((float)$revenueStats['total_collected'] / $totalInv) * 100) : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $paidPercent ?>%"></div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= 100 - $paidPercent ?>%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-2">
                    <span>Đã thu (<?= $paidPercent ?>%)</span>
                    <span>Còn nợ (<?= 100 - $paidPercent ?>%)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng chi tiết hóa đơn theo phòng -->
<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-table text-primary me-2"></i>Bảng Kê Hóa Đơn Chi Tiết Từng Phòng (Kỳ <?= e($selectedMonth) ?>)
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Phòng</th>
                    <th>Khu Trọ</th>
                    <th>Khách Thuê</th>
                    <th>Mã Hóa Đơn</th>
                    <th>Tiền Phòng</th>
                    <th>Điện & Nước</th>
                    <th>Tổng HĐ</th>
                    <th>Đã Trả</th>
                    <th>Còn Nợ</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoiceList)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            Chưa có dữ liệu hóa đơn cho kỳ tháng <?= e($selectedMonth) ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoiceList as $inv): ?>
                        <tr>
                            <td><span class="fw-bold text-dark"><?= e($inv['room_code']) ?></span></td>
                            <td class="small text-muted"><?= e($inv['area_name']) ?></td>
                            <td><?= e($inv['tenant_name']) ?></td>
                            <td><span class="font-monospace small"><?= e($inv['invoice_code']) ?></span></td>
                            <td><?= format_currency($inv['room_price']) ?></td>
                            <td><?= format_currency($inv['electricity_amount'] + $inv['water_amount']) ?></td>
                            <td class="fw-bold text-dark"><?= format_currency($inv['total_amount']) ?></td>
                            <td class="text-success"><?= format_currency($inv['paid_amount']) ?></td>
                            <td>
                                <?php if ($inv['remaining_amount'] > 0): ?>
                                    <span class="text-danger fw-bold"><?= format_currency($inv['remaining_amount']) ?></span>
                                <?php else: ?>
                                    <span class="text-success">0 đ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success">Đã thu</span>
                                <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                    <span class="badge bg-info text-dark">Thu 1 phần</span>
                                <?php elseif ($inv['status'] === 'overdue'): ?>
                                    <span class="badge bg-danger">Quá hạn</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Chưa thu</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>