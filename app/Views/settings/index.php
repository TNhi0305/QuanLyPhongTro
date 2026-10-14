<?php
// QuanLyPhongTro/app/Views/settings/index.php
// $settings là mảng key-value lấy từ database (ví dụ: $settings['app_name'])
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-gear-wide-connected text-primary me-2"></i>Cài Đặt Hệ Thống
        </h3>
        <p class="text-muted small mb-0">Quản lý các thông số ứng dụng và thông tin thanh toán chuyển khoản</p>
    </div>
</div>

<?php include __DIR__ . '/../layouts/alerts.php'; ?>

<form action="<?= url('/settings') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- 1. Thông Tin Chung & Liên Hệ -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="bi bi-info-circle me-2"></i>Thông Tin Chung & Liên Hệ
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3">
                        <label for="app_name" class="form-label fw-semibold text-secondary small">Tên Hệ Thống / Nhà
                            Trọ</label>
                        <input type="text" class="form-control" id="app_name" name="settings[app_name]"
                            value="<?= e($settings['app_name'] ?? 'Hệ Thống Quản Lý Phòng Trọ An Bình') ?>" required>
                        <div class="form-text text-muted">Tên hiển thị trên tiêu đề trang và hóa đơn.</div>
                    </div>

                    <div class="mb-3">
                        <label for="hotline" class="form-label fw-semibold text-secondary small">Số Điện Thoại
                            Hotline</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-telephone text-muted"></i></span>
                            <input type="text" class="form-control" id="hotline" name="settings[hotline]"
                                value="<?= e($settings['hotline'] ?? '0901.234.567') ?>"
                                placeholder="Ví dụ: 0901.234.567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold text-secondary small">Địa Chỉ Văn Phòng Quản
                            Lý</label>
                        <textarea class="form-control" id="address" name="settings[address]" rows="3"
                            placeholder="Nhập địa chỉ nhà trọ..."><?= e($settings['address'] ?? '123 Huỳnh Tấn Phát, P. Tân Phú, Quận 7, TP. Hồ Chí Minh') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Thông Tin Ngân Hàng & Thanh Toán -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="bi bi-bank me-2"></i>Tài Khoản Thanh Toán & VietQR
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3">
                        <label for="bank_name" class="form-label fw-semibold text-secondary small">Tên Ngân Hàng</label>
                        <input type="text" class="form-control" id="bank_name" name="settings[bank_name]"
                            value="<?= e($settings['bank_name'] ?? 'MB Bank (Ngân hàng Quân Đội)') ?>"
                            placeholder="Ví dụ: MB Bank">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="bank_account_number" class="form-label fw-semibold text-secondary small">Số Tài
                                Khoản</label>
                            <input type="text" class="form-control" id="bank_account_number"
                                name="settings[bank_account_number]"
                                value="<?= e($settings['bank_account_number'] ?? '090123456789') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="bank_qr_template" class="form-label fw-semibold text-secondary small">Mẫu Mã QR
                                VietQR</label>
                            <select class="form-select" id="bank_qr_template" name="settings[bank_qr_template]">
                                <option value="compact"
                                    <?= ($settings['bank_qr_template'] ?? '') === 'compact' ? 'selected' : '' ?>>Nhỏ gọn
                                    (compact)</option>
                                <option value="qr_only"
                                    <?= ($settings['bank_qr_template'] ?? '') === 'qr_only' ? 'selected' : '' ?>>Chỉ mã
                                    QR (qr_only)</option>
                                <option value="print"
                                    <?= ($settings['bank_qr_template'] ?? '') === 'print' ? 'selected' : '' ?>>Bản in
                                    (print)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bank_account_name" class="form-label fw-semibold text-secondary small">Tên Chủ Tài
                            Khoản</label>
                        <input type="text" class="form-control text-uppercase" id="bank_account_name"
                            name="settings[bank_account_name]"
                            value="<?= e($settings['bank_account_name'] ?? 'CHU NHA TRO AN BINH') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="payment_instruction" class="form-label fw-semibold text-secondary small">Hướng Dẫn
                            Chuyển Khoản</label>
                        <textarea class="form-control" id="payment_instruction" name="settings[payment_instruction]"
                            rows="2"><?= e($settings['payment_instruction'] ?? 'Nội dung chuyển khoản vui lòng ghi: [Mã Hóa Đơn] [Số Phòng] (Ví dụ: HDN-202610-001 P101)') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="card border-0 shadow-sm rounded-3 mt-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <span class="text-muted small">
                <i class="bi bi-shield-check text-success me-1"></i> Tất cả thông tin cài đặt sẽ được áp dụng trực tiếp
                lên hệ thống.
            </span>
            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                <i class="bi bi-floppy me-1"></i> Lưu Cài Đặt
            </button>
        </div>
    </div>
</form>