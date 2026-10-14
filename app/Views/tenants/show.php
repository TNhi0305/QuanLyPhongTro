<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-person-bounding-box text-primary me-2"></i>Hồ Sơ Cư Dân: <?= e($tenant['fullname']) ?></h4>
        <span class="text-muted small">Thông tin người thuê phòng và lịch sử các hợp đồng</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/tenants/edit/' . $tenant['id']) ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil"></i> Sửa Thông Tin
        </a>
        <a href="<?= url('/tenants') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Danh Sách
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Thẻ thông tin cá nhân -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 72px; height: 72px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1"><?= e($tenant['fullname']) ?></h5>
                <p class="text-muted small mb-3"><i class="bi bi-telephone"></i> <?= e($tenant['phone']) ?></p>

                <?php if (!empty($tenant['username'])): ?>
                    <span class="badge bg-success-subtle text-success border px-3 py-2 fs-6">
                        <i class="bi bi-shield-check me-1"></i> Tài khoản: @<?= e($tenant['username']) ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
                        Chưa có tài khoản Portal
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0 border-top">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Email:</span>
                        <span class="fw-semibold"><?= !empty($tenant['email']) ? e($tenant['email']) : '-' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">CCCD / CMND:</span>
                        <span class="fw-semibold font-monospace"><?= !empty($tenant['id_card']) ? e($tenant['id_card']) : '-' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Ngày sinh:</span>
                        <span><?= format_date($tenant['dob']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Quê quán:</span>
                        <span><?= !empty($tenant['address']) ? e($tenant['address']) : '-' ?></span>
                    </li>
                </ul>
            </div>
            <?php if (!empty($tenant['notes'])): ?>
                <div class="card-footer bg-light small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Ghi chú: <?= e($tenant['notes']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lịch sử hợp đồng thuê phòng -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-file-earmark-text text-primary me-2"></i>Lịch Sử Hợp Đồng Thuê</span>
                <a href="<?= url('/contracts/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Lập Hợp Đồng Mới
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng / Khu Trọ</th>
                            <th>Thời Hạn</th>
                            <th>Giá Thuê</th>
                            <th>Trạng Thái</th>
                            <th class="text-end">Xem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tenant['contracts'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Người thuê này chưa có hợp đồng thuê phòng nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tenant['contracts'] as $c): ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?= e($c['contract_code']) ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= e($c['room_code']) ?></div>
                                        <div class="text-muted small"><?= e($c['area_name']) ?></div>
                                    </td>
                                    <td class="small">
                                        <?= format_date($c['start_date']) ?> &rarr; <?= format_date($c['end_date']) ?>
                                    </td>
                                    <td class="fw-bold"><?= format_currency($c['rent_price']) ?></td>
                                    <td>
                                        <?php if ($c['status'] === 'active'): ?>
                                            <span class="badge bg-success">Đang hiệu lực</span>
                                        <?php elseif ($c['status'] === 'expired'): ?>
                                            <span class="badge bg-warning text-dark">Hết hạn</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Đã thanh lý</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('/contracts/show/' . $c['id']) ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
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
