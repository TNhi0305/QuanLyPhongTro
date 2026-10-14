<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-sliders text-primary me-2"></i>Bảng Giá Điện, Nước & Dịch Vụ</h4>
        <span class="text-muted small">Cài đặt đơn giá áp dụng khi lập hóa đơn cho toàn bộ khu trọ</span>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="bi bi-plus-circle"></i> Thêm Dịch Vụ Mới
    </button>
</div>

<div class="row g-4">
    <!-- Danh sách dịch vụ dạng thẻ / bảng -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã Dịch Vụ</th>
                            <th>Tên Dịch Vụ</th>
                            <th>Loại Tính Tiền</th>
                            <th>Đơn Vị Tính</th>
                            <th>Đơn Giá Hiện Tại</th>
                            <th>Trạng Thái</th>
                            <th class="text-end" style="width: 140px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $s): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= e($s['service_code']) ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= e($s['name']) ?></div>
                                    <span class="text-muted small"><?= e($s['description'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php if ($s['type'] === 'meter'): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-speedometer me-1"></i> Theo công tơ / đồng hồ
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="bi bi-calendar3 me-1"></i> Phí cố định hàng tháng
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-semibold"><?= e($s['unit']) ?></span></td>
                                <td>
                                    <span class="fw-bold text-primary fs-6"><?= format_currency($s['price']) ?></span>
                                </td>
                                <td>
                                    <?php if ($s['is_active']): ?>
                                        <span class="badge bg-success">Đang áp dụng</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tạm ngưng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>">
                                        <i class="bi bi-pencil"></i> Sửa giá
                                    </button>
                                    <form action="<?= url('/services/delete/' . $s['id']) ?>" method="POST" class="d-inline form-delete-confirm" data-confirm="Xóa dịch vụ <?= e($s['name']) ?>?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" <?= in_array($s['service_code'], ['DV-DIEN', 'DV-NUOC']) ? 'disabled' : '' ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Sửa Dịch Vụ -->
                            <div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?= url('/services/update/' . $s['id']) ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Sửa Đơn Giá: <?= e($s['name']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Tên dịch vụ <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" value="<?= e($s['name']) ?>" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold small">Đơn vị tính <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="unit" value="<?= e($s['unit']) ?>" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-bold small">Đơn giá (VNĐ) <span class="text-danger">*</span></label>
                                                        <input type="number" step="100" class="form-control" name="price" value="<?= (float)$s['price'] ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Mô tả ghi chú</label>
                                                    <textarea class="form-control" name="description" rows="2"><?= e($s['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_active" id="active<?= $s['id'] ?>" value="1" <?= $s['is_active'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-bold small" for="active<?= $s['id'] ?>">Kích hoạt dịch vụ này</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Dịch Vụ Mới -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/services') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i>Thêm Dịch Vụ Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Mã Dịch Vụ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" name="service_code" placeholder="DV-WIFI, DV-RAC..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Loại Dịch Vụ</label>
                            <select name="type" class="form-select">
                                <option value="fixed" selected>Phí cố định hàng tháng</option>
                                <option value="meter">Theo chỉ số đo đếm</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Tên Dịch Vụ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Ví dụ: Giặt sấy chung, Thẻ từ thang máy..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Đơn Vị Tính <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="unit" placeholder="Phòng, Người, Xe, Tháng..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Đơn Giá (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" step="500" class="form-control" name="price" placeholder="50000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Mô Tả</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Ghi chú chi tiết dịch vụ..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Thêm Dịch Vụ</button>
                </div>
            </form>
        </div>
    </div>
</div>
