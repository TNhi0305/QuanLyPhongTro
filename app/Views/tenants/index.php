<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Quản Lý Người Thuê Phòng</h4>
        <span class="text-muted small">Danh sách thông tin cư dân, hợp đồng và tài khoản người thuê</span>
    </div>
    <a href="<?= url('/tenants/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus-fill"></i> Thêm Người Thuê Mới
    </a>
</div>

<!-- Tìm kiếm -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="<?= url('/tenants') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" name="keyword" value="<?= e($keyword ?? '') ?>" placeholder="Tìm kiếm theo họ tên, số điện thoại, CCCD hoặc mã phòng...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Tìm Kiếm
                </button>
                <a href="<?= url('/tenants') ?>" class="btn btn-outline-secondary" title="Xem tất cả">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng người thuê -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Họ & Tên</th>
                    <th>Số Điện Thoại</th>
                    <th>CCCD / CMND</th>
                    <th>Phòng Đang Thuê</th>
                    <th>Tài Khoản Portal</th>
                    <th class="text-end" style="width: 150px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tenants)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                            Không tìm thấy người thuê nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tenants as $t): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($t['fullname']) ?></div>
                                <span class="text-muted small"><?= !empty($t['email']) ? e($t['email']) : 'Chưa có email' ?></span>
                            </td>
                            <td>
                                <span class="fw-semibold text-primary"><i class="bi bi-telephone me-1"></i><?= e($t['phone']) ?></span>
                            </td>
                            <td>
                                <span class="font-monospace text-secondary"><?= !empty($t['id_card']) ? e($t['id_card']) : '-' ?></span>
                            </td>
                            <td>
                                <?php if (!empty($t['room_code'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">
                                        <i class="bi bi-door-open-fill me-1"></i> <?= e($t['room_code']) ?> (<?= e($t['area_name']) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Chưa thuê phòng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($t['username'])): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-person-check me-1"></i> @<?= e($t['username']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Chưa cấp</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/tenants/show/' . $t['id']) ?>" class="btn btn-outline-info" title="Xem hồ sơ">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('/tenants/edit/' . $t['id']) ?>" class="btn btn-outline-primary" title="Sửa thông tin">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= url('/tenants/delete/' . $t['id']) ?>" method="POST" class="d-inline form-delete-confirm" data-confirm="Bạn có chắc chắn muốn xóa khách thuê <?= e($t['fullname']) ?> không?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa khách thuê" <?= !empty($t['contract_id']) ? 'disabled' : '' ?>>
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
