<div class="row g-3 mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h4 class="fw-bold mb-0">Bảng Điều Khiển Quản Trị</h4>
            <span class="text-muted small">Cập nhật lúc <?= date('H:i, d/m/Y') ?> &bull; Kỳ tháng <?= date('m/Y') ?></span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/invoices/create') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-receipt"></i> Lập Hóa Đơn
            </a>
            <a href="<?= url('/meters/create') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-lightning-charge"></i> Ghi Điện Nước
            </a>
            <a href="<?= url('/rooms/create') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-plus-lg"></i> Thêm Phòng
            </a>
        </div>
    </div>
</div>

<!-- 1. Thẻ Thống Kê Tổng Quan (Stats Cards) -->
<div class="row g-3 mb-4">
    <!-- Doanh thu tháng -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Hóa Đơn Tháng <?= date('m') ?></span>
                    <h4 class="fw-bold text-primary mb-0 mt-1"><?= format_currency($stats['month_revenue'] ?? 0) ?></h4>
                    <span class="text-success small fw-medium"><i class="bi bi-check-circle"></i> Đã thu: <?= format_currency($stats['month_paid'] ?? 0) ?></span>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng Công Nợ -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Tổng Công Nợ</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1"><?= format_currency($stats['total_debt'] ?? 0) ?></h4>
                    <span class="text-muted small">Tổng thu: <?= format_currency($stats['total_collected'] ?? 0) ?></span>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tình trạng phòng -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Phòng Trọ (<?= $stats['total_rooms'] ?> phòng)</span>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <h4 class="fw-bold text-success mb-0"><?= $stats['rented_rooms'] ?></h4>
                        <span class="small text-muted">đang thuê</span>
                    </div>
                    <div class="small mt-1">
                        <span class="badge bg-secondary"><?= $stats['empty_rooms'] ?> trống</span>
                        <span class="badge bg-warning text-dark"><?= $stats['maintenance_rooms'] ?> bảo trì</span>
                    </div>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-door-open-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Khách thuê & Khu trọ -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Khách Thuê & Khu Trọ</span>
                    <h4 class="fw-bold text-info mb-0 mt-1"><?= $stats['total_tenants'] ?> Khách</h4>
                    <span class="text-muted small"><i class="bi bi-buildings"></i> <?= $stats['total_areas'] ?> dãy / khu trọ</span>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Biểu Đồ Doanh Thu & Thống Kê Phòng -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-fill text-primary me-2"></i>Biểu Đồ Tài Chính (6 Tháng Gần Nhất)</span>
                <span class="badge bg-light text-secondary border">Tự động cập nhật</span>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" style="max-height: 280px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>Tỷ Lệ Lấp Đầy Phòng
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <canvas id="roomPieChart" style="max-height: 180px;"></canvas>
                <div class="mt-3 w-100 text-start small">
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <span><i class="bi bi-circle-fill text-success me-1"></i> Đang thuê:</span>
                        <strong><?= $stats['rented_rooms'] ?> phòng (<?= $stats['total_rooms'] > 0 ? round(($stats['rented_rooms'] / $stats['total_rooms']) * 100) : 0 ?>%)</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <span><i class="bi bi-circle-fill text-secondary me-1"></i> Phòng trống:</span>
                        <strong><?= $stats['empty_rooms'] ?> phòng</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span><i class="bi bi-circle-fill text-warning me-1"></i> Đang bảo trì:</span>
                        <strong><?= $stats['maintenance_rooms'] ?> phòng</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. Hóa Đơn Quá Hạn & Sắp Đến Hạn -->
<div class="row g-3 mb-4">
    <!-- Quá hạn -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center bg-danger bg-opacity-10 text-danger border-danger border-opacity-25">
                <span class="fw-bold"><i class="bi bi-exclamation-octagon-fill me-2"></i>Hóa Đơn Quá Hạn Thanh Toán (<?= count($overdueInvoices) ?>)</span>
                <a href="<?= url('/invoices?status=overdue') ?>" class="text-danger small fw-semibold text-decoration-none">Xem tất cả &raquo;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng / Khách</th>
                            <th>Hạn trả</th>
                            <th>Còn nợ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($overdueInvoices)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Tuyệt vời! Không có hóa đơn nào bị quá hạn.</td></tr>
                        <?php else: ?>
                            <?php foreach ($overdueInvoices as $inv): ?>
                                <tr>
                                    <td><span class="fw-semibold text-dark"><?= e($inv['invoice_code']) ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= e($inv['room_code']) ?></div>
                                        <div class="text-muted small"><?= e($inv['tenant_name']) ?></div>
                                    </td>
                                    <td class="text-danger fw-semibold small"><?= format_date($inv['due_date']) ?></td>
                                    <td class="text-danger fw-bold"><?= format_currency($inv['remaining_amount']) ?></td>
                                    <td>
                                        <a href="<?= url('/invoices/show/' . $inv['id']) ?>" class="btn btn-outline-danger btn-sm">Thu tiền</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sắp đến hạn -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning bg-opacity-10 text-dark border-warning border-opacity-25">
                <span class="fw-bold"><i class="bi bi-clock-history text-warning me-2"></i>Hóa Đơn Sắp Đến Hạn (5 Ngày Tới)</span>
                <a href="<?= url('/invoices') ?>" class="text-dark small fw-semibold text-decoration-none">Xem tất cả &raquo;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng / Khách</th>
                            <th>Hạn trả</th>
                            <th>Số tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($upcomingInvoices)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Không có hóa đơn nào sắp đến hạn trong 5 ngày tới.</td></tr>
                        <?php else: ?>
                            <?php foreach ($upcomingInvoices as $inv): ?>
                                <tr>
                                    <td><span class="fw-semibold text-dark"><?= e($inv['invoice_code']) ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= e($inv['room_code']) ?></div>
                                        <div class="text-muted small"><?= e($inv['tenant_name']) ?></div>
                                    </td>
                                    <td class="text-warning text-dark fw-medium small"><?= format_date($inv['due_date']) ?></td>
                                    <td class="fw-bold"><?= format_currency($inv['remaining_amount']) ?></td>
                                    <td>
                                        <a href="<?= url('/invoices/show/' . $inv['id']) ?>" class="btn btn-outline-primary btn-sm">Chi tiết</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 4. Yêu Cầu Sửa Chữa Chờ Xử Lý & Nhật Ký Hoạt Động -->
<div class="row g-3">
    <!-- Yêu cầu bảo trì -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-tools text-primary me-2"></i>Yêu Cầu Sửa Chữa Chờ Xử Lý</span>
                <a href="<?= url('/maintenance') ?>" class="small text-decoration-none">Quản lý &raquo;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentMaintenance)): ?>
                    <div class="text-center text-muted py-4">Hiện không có sự cố nào đang báo hỏng.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentMaintenance as $req): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark"><?= e($req['title']) ?></div>
                                    <div class="small text-muted">
                                        Phòng <span class="badge bg-light text-dark border"><?= e($req['room_code']) ?></span> &bull; 
                                        Người gửi: <?= e($req['tenant_name']) ?> &bull; 
                                        <?= format_datetime($req['created_at']) ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-<?= $req['priority'] === 'urgent' ? 'danger' : ($req['priority'] === 'high' ? 'warning' : 'info') ?> me-2">
                                        <?= match($req['priority']) { 'urgent' => 'Khẩn cấp', 'high' => 'Cao', 'medium' => 'TB', default => 'Thấp' } ?>
                                    </span>
                                    <a href="<?= url('/maintenance/show/' . $req['id']) ?>" class="btn btn-sm btn-outline-primary">Xử lý</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Nhật ký gần đây -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-bold">
                <i class="bi bi-clock-history text-primary me-2"></i>Nhật Ký Thao Tác Gần Đây
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php if (empty($recentLogs)): ?>
                        <li class="list-group-item text-center text-muted py-3">Chưa có nhật ký ghi nhận.</li>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <span class="fw-semibold text-dark"><?= e($log['action']) ?></span>: 
                                    <span class="text-muted"><?= e($log['details']) ?></span>
                                </div>
                                <span class="text-muted" style="font-size: 0.75rem; white-space: nowrap;"><?= format_datetime($log['created_at'], 'H:i d/m') ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Script vẽ biểu đồ Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Biểu đồ cột Doanh thu & Chi phí
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['labels']) ?>,
                datasets: [
                    {
                        label: 'Hóa Đơn Phát Hành',
                        data: <?= json_encode($chartData['invoiced']) ?>,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderRadius: 4
                    },
                    {
                        label: 'Tiền Đã Thu',
                        data: <?= json_encode($chartData['collected']) ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderRadius: 4
                    },
                    {
                        label: 'Chi Phí Vận Hành',
                        data: <?= json_encode($chartData['expenses']) ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return (val / 1000000) + ' Tr';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Biểu đồ tròn Trạng thái phòng
    const ctxPie = document.getElementById('roomPieChart');
    if (ctxPie) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Đang thuê', 'Phòng trống', 'Bảo trì'],
                datasets: [{
                    data: [<?= $stats['rented_rooms'] ?>, <?= $stats['empty_rooms'] ?>, <?= $stats['maintenance_rooms'] ?>],
                    backgroundColor: ['#10b981', '#94a3b8', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
