<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Thêm Khoản Chi Phí Mới</h4>
                <span class="text-muted small">Ghi nhận chi phí phát sinh cho việc bảo trì, vệ sinh, vật tư nhà trọ</span>
            </div>
            <a href="<?= url('/expenses') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/expenses') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label fw-bold small">Tên / Nội Dung Chi Phí <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Ví dụ: Thay bóng đèn hành lang, Tiền vệ sinh rác..." required autofocus>
                        </div>

                        <div class="col-md-4">
                            <label for="category" class="form-label fw-bold small">Hạng Mục Chi <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-select">
                                <option value="Bảo trì" selected>Bảo trì / Sửa chữa</option>
                                <option value="Điện nước chung">Điện nước chung</option>
                                <option value="Vệ sinh">Vệ sinh & Rác</option>
                                <option value="Mua sắm">Mua sắm thiết bị</option>
                                <option value="Lương">Lương nhân viên / bảo vệ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-bold small">Số Tiền Chi (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" step="1000" class="form-control" id="amount" name="amount" placeholder="Ví dụ: 350000" required>
                        </div>

                        <div class="col-md-6">
                            <label for="expense_date" class="form-label fw-bold small">Ngày Chi Tiền <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="recipient" class="form-label fw-bold small">Người / Đơn Vị Nhận Tiền</label>
                            <input type="text" class="form-control" id="recipient" name="recipient" placeholder="Tên thợ, cửa hàng, công ty môi trường...">
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold small">Chi Tiết / Ghi Chú Khoản Chi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ghi chú hóa đơn chứng từ, địa điểm mua..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/expenses') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Phiếu Chi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
