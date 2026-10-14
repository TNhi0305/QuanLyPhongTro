<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Sửa Hợp Đồng: <?= e($contract['contract_code']) ?></h4>
                <span class="text-muted small">Cập nhật giá thỏa thuận, tiền cọc hoặc điều khoản hợp đồng</span>
            </div>
            <a href="<?= url('/contracts/show/' . $contract['id']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/contracts/update/' . $contract['id']) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Phòng thuê</label>
                            <input type="text" class="form-control bg-light" value="<?= e($contract['room_code']) ?> - <?= e($contract['room_number']) ?>" disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Người thuê</label>
                            <input type="text" class="form-control bg-light" value="<?= e($contract['tenant_name']) ?>" disabled>
                        </div>

                        <div class="col-md-4">
                            <label for="rent_price" class="form-label fw-bold small">Giá Thuê Đã Thỏa Thuận (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" step="10000" class="form-control" id="rent_price" name="rent_price" value="<?= (float)$contract['rent_price'] ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="deposit_amount" class="form-label fw-bold small">Tiền Đặt Cọc (VNĐ)</label>
                            <input type="number" step="10000" class="form-control" id="deposit_amount" name="deposit_amount" value="<?= (float)$contract['deposit_amount'] ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="payment_cycle" class="form-label fw-bold small">Chu Kỳ Trả Tiền (Tháng)</label>
                            <input type="number" class="form-control" id="payment_cycle" name="payment_cycle" value="<?= (int)$contract['payment_cycle'] ?>" min="1" max="12">
                        </div>

                        <div class="col-12">
                            <label for="terms" class="form-label fw-bold small">Điều Khoản Hợp Đồng & Ghi Chú</label>
                            <textarea class="form-control" id="terms" name="terms" rows="5"><?= e($contract['terms'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/contracts/show/' . $contract['id']) ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
