<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Quản Lý Hóa Đơn & Thu Tiền</h4>
        <span class="text-muted small">Phát hành hóa đơn tiền phòng, tiền điện nước và theo dõi tình trạng thanh toán</span>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#autoGenerateModal">
            <i class="bi bi-magic"></i> Tự Động Sinh Hàng Loạt
        </button>
        <a href="<?= url('/invoices/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Lập Hóa Đơn Thủ Công
        </a>
    </div>
</div>

<!-- Bộ lọc tìm kiếm -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="<?= url('/invoices') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" class="form-control" name="keyword" value="<?= e($filters['keyword'] ?? '') ?>" placeholder="Tìm mã HĐN, số phòng, tên khách...">
            </div>
            <div class="col-md-3">
                <input type="month" class="form-control" name="billing_month" value="<?= e($filters['billing_month'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="unpaid" <?= ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="partially_paid" <?= ($filters['status'] ?? '') === 'partially_paid' ? 'selected' : '' ?>>Thanh toán 1 phần</option>
                    <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Quá hạn</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="area_id" class="form-select">
                    <option value="">-- Tất cả khu trọ --</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($filters['area_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
                <a href="<?= url('/invoices') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách hóa đơn -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Hóa Đơn</th>
                    <th>Phòng / Khách Thuê</th>
                    <th>Kỳ Tháng</th>
                    <th>Tổng Hóa Đơn</th>
                    <th>Đã Thu</th>
                    <th>Còn Phải Thu</th>
                    <th>Hạn Trả</th>
                    <th>Trạng Thái</th>
                    <th class="text-end" style="width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            Không tìm thấy hóa đơn nào phù hợp.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/invoices/show/' . $inv['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= e($inv['invoice_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($inv['room_code']) ?></div>
                                <span class="text-muted small"><?= e($inv['tenant_name']) ?></span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($inv['billing_month']) ?></span></td>
                            <td><span class="fw-bold text-dark"><?= format_currency($inv['total_amount']) ?></span></td>
                            <td><span class="text-success fw-medium"><?= format_currency($inv['paid_amount']) ?></span></td>
                            <td>
                                <?php if ($inv['remaining_amount'] > 0): ?>
                                    <span class="text-danger fw-bold"><?= format_currency($inv['remaining_amount']) ?></span>
                                <?php else: ?>
                                    <span class="text-success fw-semibold">0 đ</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <span class="<?= $inv['status'] === 'overdue' ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= format_date($inv['due_date']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success badge-status"><i class="bi bi-check-circle me-1"></i> Đã thanh toán</span>
                                <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                    <span class="badge bg-info text-dark badge-status"><i class="bi bi-pie-chart me-1"></i> Trả 1 phần</span>
                                <?php elseif ($inv['status'] === 'overdue'): ?>
                                    <span class="badge bg-danger badge-status"><i class="bi bi-exclamation-triangle me-1"></i> Quá hạn</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark badge-status"><i class="bi bi-clock me-1"></i> Chưa trả</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/invoices/show/' . $inv['id']) ?>" class="btn btn-outline-primary" title="Xem chi tiết & thu tiền">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('/invoices/print/' . $inv['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="In biên lai">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tự Động Sinh Hóa Đơn Hàng Loạt -->
<div class="modal fade" id="autoGenerateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/invoices/auto-generate') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-magic text-primary me-2"></i>Tự Động Sinh Hóa Đơn Hàng Loạt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Hệ thống sẽ quét toàn bộ hợp đồng đang hoạt động, lấy chỉ số điện nước đã ghi và các dịch vụ cố định (Wifi, rác...) để tự động sinh hóa đơn.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kỳ Tính Tiền (Tháng/Năm) <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" name="billing_month" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Hạn Thanh Toán <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="due_date" value="<?= date('Y-m-20') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> Bắt Đầu Phát Hành</button>
                </div>
            </form>
        </div>
    </div>
</div>
