<?php

/**
 * @var array $tenant
 * @var array $contract
 * @var array $currentInvoice
 * @var array $notifications
 */
?>

<!-- Chào mừng & Thông tin phòng -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="bg-primary text-white p-4 rounded-4 shadow-sm position-relative overflow-hidden">
            <div class="position-relative z-1">
                <span class="badge bg-white text-primary mb-2 fw-bold">CỔNG DỊCH VỤ CƯ DÂN</span>
                <h3 class="fw-bold mb-1">Xin chào, <?= e($tenant['fullname']) ?>!</h3>
                <p class="mb-0 text-white-50">
                    <?php if ($contract): ?>
                        Bạn đang thuê phòng <strong class="text-white"><?= e($contract['room_code']) ?></strong> thuộc <?= e($contract['area_name']) ?>.
                    <?php else: ?>
                        Tài khoản hiện chưa gắn với hợp đồng thuê phòng nào.
                    <?php endif; ?>
                </p>
            </div>
            <i class="bi bi-houses position-absolute end-0 bottom-0 text-white text-opacity-10" style="font-size: 8rem; margin-right: -20px; margin-bottom: -30px;"></i>
        </div>
    </div>
</div>

<!-- Trạng thái Hóa đơn tháng này -->
<?php if ($currentInvoice): ?>
    <div class="card mb-4 shadow-sm border-start border-4 border-<?= $currentInvoice['remaining_amount'] > 0 ? 'warning' : 'success' ?>">
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="fw-bold mb-0 text-dark">Hóa Đơn Tháng <?= e($currentInvoice['billing_month']) ?></h5>
                    <?php if ($currentInvoice['status'] === 'paid'): ?>
                        <span class="badge bg-success">Đã thanh toán</span>
                    <?php elseif ($currentInvoice['status'] === 'partially_paid'): ?>
                        <span class="badge bg-info text-dark">Thanh toán 1 phần</span>
                    <?php elseif ($currentInvoice['status'] === 'overdue'): ?>
                        <span class="badge bg-danger">Quá hạn</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small">
                    Tổng tiền: <strong><?= format_currency($currentInvoice['total_amount']) ?></strong> &bull; Hạn thanh toán: <?= format_date($currentInvoice['due_date']) ?>
                </div>
            </div>
            <div class="text-sm-end">
                <?php if ($currentInvoice['remaining_amount'] > 0): ?>
                    <div class="small text-danger fw-bold">Còn phải trả: <?= format_currency($currentInvoice['remaining_amount']) ?></div>
                    <a href="<?= url('/tenant/invoices/show/' . $currentInvoice['id']) ?>" class="btn btn-primary btn-sm mt-1">
                        <i class="bi bi-qr-code me-1"></i> Xem & Quét QR Trả Tiền
                    </a>
                <?php else: ?>
                    <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Đã hoàn tất</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Các nút chức năng nhanh (Quick Actions) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= url('/tenant/room') ?>" class="card text-decoration-none h-100 p-3 text-center stat-card border">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-2">
                <i class="bi bi-door-open-fill"></i>
            </div>
            <span class="fw-bold text-dark small">Phòng Đang Thuê</span>
            <span class="text-muted" style="font-size: 0.75rem;">Chi tiết & hình ảnh</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('/tenant/invoices') ?>" class="card text-decoration-none h-100 p-3 text-center stat-card border">
            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-2">
                <i class="bi bi-receipt"></i>
            </div>
            <span class="fw-bold text-dark small">Hóa Đơn & Tiền Nhà</span>
            <span class="text-muted" style="font-size: 0.75rem;">Lịch sử & biên lai</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('/tenant/meters') ?>" class="card text-decoration-none h-100 p-3 text-center stat-card border">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-2">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <span class="fw-bold text-dark small">Chỉ Số Điện Nước</span>
            <span class="text-muted" style="font-size: 0.75rem;">Theo dõi tiêu thụ</span>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= url('/tenant/maintenance/create') ?>" class="card text-decoration-none h-100 p-3 text-center stat-card border">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-2">
                <i class="bi bi-tools"></i>
            </div>
            <span class="fw-bold text-dark small">Báo Hỏng Thiết Bị</span>
            <span class="text-muted" style="font-size: 0.75rem;">Gửi yêu cầu sửa chữa</span>
        </a>
    </div>
</div>

<!-- Thông báo từ chủ trọ & Tiến độ sửa chữa -->
<div class="row g-3">
    <!-- Thông báo -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-bell-fill text-warning me-2"></i>Thông Báo Từ Chủ Trọ</span>
                <a href="<?= url('/tenant/notifications') ?>" class="small text-decoration-none">Xem tất cả &raquo;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="text-center text-muted py-4 small">Chưa có thông báo mới nào.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifications as $n): ?>
                            <li class="list-group-item py-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold text-dark mb-0"><?= e($n['title']) ?></h6>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= format_date($n['created_at']) ?></span>
                                </div>
                                <p class="text-secondary small mb-0 text-truncate"><?= e($n['content']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tiến độ yêu cầu sửa chữa -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-tools text-primary me-2"></i>Yêu Cầu Sửa Chữa Đã Gửi</span>
                <a href="<?= url('/tenant/maintenance') ?>" class="small text-decoration-none">Xem tất cả &raquo;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentRequests)): ?>
                    <div class="text-center text-muted py-4 small">
                        Bạn chưa gửi yêu cầu báo hỏng nào.
                        <div class="mt-2">
                            <a href="<?= url('/tenant/maintenance/create') ?>" class="btn btn-sm btn-outline-primary">Gửi yêu cầu nếu thiết bị hư hỏng</a>
                        </div>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentRequests as $req): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark"><?= e($req['title']) ?></div>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= format_datetime($req['created_at']) ?></span>
                                </div>
                                <div>
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                    <?php elseif ($req['status'] === 'in_progress'): ?>
                                        <span class="badge bg-info text-dark">Đang xử lý</span>
                                    <?php elseif ($req['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Đã hoàn thành</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Từ chối</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>