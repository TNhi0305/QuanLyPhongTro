<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-tools text-primary me-2"></i>Quản Lý Yêu Cầu Sửa Chữa & Bảo Trì</h4>
        <span class="text-muted small">Tiếp nhận và cập nhật tiến độ xử lý sự cố thiết bị từ người thuê</span>
    </div>
</div>

<!-- Bộ lọc trạng thái -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="<?= url('/maintenance') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>Đang xử lý</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
                    <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                </select>
            </div>
            <div class="col-md-5">
                <select name="priority" class="form-select">
                    <option value="">-- Tất cả mức độ ưu tiên --</option>
                    <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Khẩn cấp</option>
                    <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Cao</option>
                    <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Trung bình</option>
                    <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Thấp</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
                <a href="<?= url('/maintenance') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng yêu cầu -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Yêu Cầu</th>
                    <th>Phòng / Người Báo</th>
                    <th>Tiêu Đề Sự Cố</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Ngày Gửi</th>
                    <th>Chi Phí Sửa</th>
                    <th>Trạng Thái</th>
                    <th class="text-end" style="width: 120px;">Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                            Không có yêu cầu bảo trì nào cần xử lý.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= e($r['request_code']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($r['room_code']) ?></div>
                                <span class="text-muted small"><?= e($r['tenant_name']) ?> (<?= e($r['tenant_phone']) ?>)</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($r['title']) ?></div>
                                <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;">
                                    <?= e($r['description']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= match($r['priority']) { 'urgent' => 'danger', 'high' => 'warning text-dark', 'medium' => 'info text-dark', default => 'secondary' } ?>">
                                    <?= match($r['priority']) { 'urgent' => 'Khẩn cấp', 'high' => 'Ưu tiên cao', 'medium' => 'Trung bình', default => 'Thấp' } ?>
                                </span>
                            </td>
                            <td class="small text-muted">
                                <?= format_datetime($r['created_at']) ?>
                            </td>
                            <td>
                                <?php if ($r['cost'] > 0): ?>
                                    <span class="fw-bold text-danger"><?= format_currency($r['cost']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <span class="badge bg-danger badge-status"><i class="bi bi-clock me-1"></i> Chờ xử lý</span>
                                <?php elseif ($r['status'] === 'in_progress'): ?>
                                    <span class="badge bg-warning text-dark badge-status"><i class="bi bi-hourglass-split me-1"></i> Đang sửa</span>
                                <?php elseif ($r['status'] === 'completed'): ?>
                                    <span class="badge bg-success badge-status"><i class="bi bi-check-circle me-1"></i> Hoàn thành</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary badge-status">Từ chối</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/maintenance/show/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-gear"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
