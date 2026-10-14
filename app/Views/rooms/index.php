<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-door-open-fill text-primary me-2"></i>Quản Lý Phòng Trọ</h4>
        <span class="text-muted small">Danh sách và tình trạng thực tế của tất cả các phòng trọ</span>
    </div>
    <a href="<?= url('/rooms/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm Phòng Mới
    </a>
</div>

<!-- Bộ Lọc & Tìm Kiếm -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="<?= url('/rooms') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" class="form-control" name="keyword" value="<?= e($filters['keyword'] ?? '') ?>" placeholder="Tìm số phòng, mã phòng...">
            </div>
            <div class="col-md-3">
                <select name="area_id" class="form-select">
                    <option value="">-- Tất cả khu trọ --</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($filters['area_id'] ?? '') == $a['id'] ? 'selected' : '' ?>>
                            <?= e($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="empty" <?= ($filters['status'] ?? '') === 'empty' ? 'selected' : '' ?>>Phòng Trống</option>
                    <option value="rented" <?= ($filters['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Đang Cho Thuê</option>
                    <option value="maintenance" <?= ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Đang Bảo Trì</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="<?= url('/rooms') ?>" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách phòng dạng bảng responsive và thẻ -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">Hình ảnh</th>
                    <th>Phòng / Khu Trọ</th>
                    <th>Diện tích</th>
                    <th>Giá Thuê / Cọc</th>
                    <th>Trạng Thái</th>
                    <th>Khách Đang Thuê</th>
                    <th class="text-end" style="width: 160px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-door-closed fs-1 d-block mb-2"></i>
                            Không tìm thấy phòng trọ nào phù hợp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td>
                                <?php if (!empty($r['primary_image'])): ?>
                                    <img src="<?= upload_url($r['primary_image']) ?>" class="rounded object-fit-cover shadow-sm" style="width: 60px; height: 60px;" alt="<?= e($r['room_code']) ?>">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                        <i class="bi bi-image fs-4"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($r['room_code']) ?> - <?= e($r['room_number']) ?></div>
                                <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?= e($r['area_name']) ?></div>
                            </td>
                            <td>
                                <div><?= (float)$r['area_size'] ?> m²</div>
                                <span class="text-muted small">Tối đa <?= (int)$r['max_occupants'] ?> người</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary"><?= format_currency($r['price']) ?>/tháng</div>
                                <div class="text-muted small">Cọc: <?= format_currency($r['deposit']) ?></div>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'empty'): ?>
                                    <span class="badge bg-secondary badge-status"><i class="bi bi-circle-fill me-1 small"></i> Trống</span>
                                <?php elseif ($r['status'] === 'rented'): ?>
                                    <span class="badge bg-success badge-status"><i class="bi bi-check-circle-fill me-1 small"></i> Đang thuê</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark badge-status"><i class="bi bi-tools me-1 small"></i> Bảo trì</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($r['current_tenant_name'])): ?>
                                    <div class="fw-semibold text-dark"><?= e($r['current_tenant_name']) ?></div>
                                    <div class="text-muted small"><i class="bi bi-telephone"></i> <?= e($r['current_tenant_phone']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted font-monospace small">- Chưa có -</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/rooms/show/' . $r['id']) ?>" class="btn btn-outline-info" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('/rooms/edit/' . $r['id']) ?>" class="btn btn-outline-primary" title="Sửa phòng">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= url('/rooms/delete/' . $r['id']) ?>" method="POST" class="d-inline form-delete-confirm" data-confirm="Bạn có chắc muốn xóa phòng <?= e($r['room_code']) ?>?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa phòng" <?= !empty($r['current_contract_id']) ? 'disabled' : '' ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
