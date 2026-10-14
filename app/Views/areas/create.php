<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Thêm Khu Trọ Mới</h4>
                <span class="text-muted small">Tạo thêm khu vực hoặc dãy nhà trọ quản lý</span>
            </div>
            <a href="<?= url('/areas') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/areas') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="area_code" class="form-label fw-bold small">Mã Khu Trọ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="area_code" name="area_code" placeholder="Ví dụ: KT-Q7, DAY-A..." required autofocus>
                            <div class="form-text">Mã định danh duy nhất (không dấu).</div>
                        </div>

                        <div class="col-md-8">
                            <label for="name" class="form-label fw-bold small">Tên Khu Trọ / Dãy Trọ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Ví dụ: Khu Trọ An Bình - Quận 7" required>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label fw-bold small">Địa Chỉ Đầy Đủ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Ví dụ: Số 123 Huỳnh Tấn Phát, P. Tân Phú, Quận 7, TP.HCM" required>
                        </div>

                        <div class="col-md-6">
                            <label for="contact_phone" class="form-label fw-bold small">Số Điện Thoại Liên Hệ</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" placeholder="Số điện thoại quản lý...">
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold small">Mô Tả / Tiện Ích Chung</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ghi chú về an ninh, camera, bãi xe, giờ giấc tự do..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/areas') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Khu Trọ Mới
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
