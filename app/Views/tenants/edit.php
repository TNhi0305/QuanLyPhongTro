<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Sửa Thông Tin Khách Thuê</h4>
                <span class="text-muted small">Cập nhật hồ sơ: <?= e($tenant['fullname']) ?></span>
            </div>
            <a href="<?= url('/tenants/show/' . $tenant['id']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Xem Hồ Sơ
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/tenants/update/' . $tenant['id']) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fullname" class="form-label fw-bold small">Họ Và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?= e($tenant['fullname']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold small">Số Điện Thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= e($tenant['phone']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold small">Địa Chỉ Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($tenant['email'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="dob" class="form-label fw-bold small">Ngày Sinh</label>
                            <input type="date" class="form-control" id="dob" name="dob" value="<?= e($tenant['dob'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="id_card" class="form-label fw-bold small">Số Giấy Tờ Tùy Thân (CCCD / CMND)</label>
                            <input type="text" class="form-control" id="id_card" name="id_card" value="<?= e($tenant['id_card'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label fw-bold small">Địa Chỉ Thường Trú / Quê Quán</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?= e($tenant['address'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-bold small">Ghi Chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= e($tenant['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/tenants') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Cập Nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
