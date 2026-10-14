<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Quản Lý Hợp Đồng Thuê</h4>
        <span class="text-muted small">Danh sách hợp đồng thuê phòng, thời hạn và trạng thái hiệu lực</span>
    </div>
    <a href="<?= url('/contracts/create') ?>" class="btn btn-primary">
        <i class="bi bi-file-earmark-plus"></i> Tạo Hợp Đồng Mới
    </a>
</div>

<!-- Bộ Lọc -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="<?= url('/contracts') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control" name="keyword" value="<?= e($filters['keyword'] ?? '') ?>" placeholder="Tìm mã hợp đồng, số phòng, tên khách...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang hiệu lực</option>
                    <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Hết hạn</option>
                    <option value="terminated" <?= ($filters['status'] ?? '') === 'terminated' ? 'selected' : '' ?>>Đã chấm dứt</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="area_id" class="form-select">
                    <option value="">-- Tất cả khu trọ --</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($filters['area_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Lọc</button>
                <a href="<?= url('/contracts') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng hợp đồng -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Hợp Đồng</th>
                    <th>Phòng / Khu Trọ</th>
                    <th>Khách Thuê</th>
                    <th>Thời Hạn HĐ</th>
                    <th>Giá Thuê / Cọc</th>
                    <th>Trạng Thái</th>
                    <th class="text-end" style="width: 170px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contracts)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                            Không tìm thấy hợp đồng nào phù hợp.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/contracts/show/' . $c['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= e($c['contract_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($c['room_code']) ?> - <?= e($c['room_number']) ?></div>
                                <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?= e($c['area_name']) ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($c['tenant_name']) ?></div>
                                <span class="text-muted small"><i class="bi bi-telephone"></i> <?= e($c['tenant_phone']) ?></span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= format_date($c['start_date']) ?> &rarr; <?= format_date($c['end_date']) ?></div>
                                <span class="text-muted small">Kỳ trả: <?= (int)$c['payment_cycle'] ?> tháng/lần</span>
                            </td>
                            <td>
                                <div class="fw-bold text-success"><?= format_currency($c['rent_price']) ?></div>
                                <span class="text-muted small">Cọc: <?= format_currency($c['deposit_amount']) ?></span>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'active'): ?>
                                    <span class="badge bg-success badge-status"><i class="bi bi-check-circle-fill me-1 small"></i> Hiệu lực</span>
                                <?php elseif ($c['status'] === 'expired'): ?>
                                    <span class="badge bg-warning text-dark badge-status"><i class="bi bi-clock-history me-1 small"></i> Hết hạn</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary badge-status"><i class="bi bi-x-circle-fill me-1 small"></i> Đã chấm dứt</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/contracts/show/' . $c['id']) ?>" class="btn btn-outline-info" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('/contracts/print/' . $c['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="In hợp đồng">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="<?= url('/contracts/edit/' . $c['id']) ?>" class="btn btn-outline-primary" title="Sửa hợp đồng">
                                        <i class="bi bi-pencil"></i>
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
