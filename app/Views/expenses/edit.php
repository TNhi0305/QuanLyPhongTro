<?php
/** @var array $expense */
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Sửa Khoản Chi Phí: <?= e($expense['expense_code']) ?></h4>
                <span class="text-muted small">Cập nhật nội dung, số tiền hoặc ngày chi</span>
            </div>
            <a href="<?= url('/expenses') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/expenses/update/' . $expense['id']) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label fw-bold small">Tên / Nội Dung Chi Phí <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= e($expense['title']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="category" class="form-label fw-bold small">Hạng Mục Chi <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-select">
                                <option value="Bảo trì" <?= $expense['category'] === 'Bảo trì' ? 'selected' : '' ?>>Bảo trì / Sửa chữa</option>
                                <option value="Điện nước chung" <?= $expense['category'] === 'Điện nước chung' ? 'selected' : '' ?>>Điện nước chung</option>
                                <option value="Vệ sinh" <?= $expense['category'] === 'Vệ sinh' ? 'selected' : '' ?>>Vệ sinh & Rác</option>
                                <option value="Mua sắm" <?= $expense['category'] === 'Mua sắm' ? 'selected' : '' ?>>Mua sắm thiết bị</option>
                                <option value="Lương" <?= $expense['category'] === 'Lương' ? 'selected' : '' ?>>Lương nhân viên / bảo vệ</option>
                                <option value="Khác" <?= $expense['category'] === 'Khác' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-bold small">Số Tiền Chi (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" step="1000" class="form-control" id="amount" name="amount" value="<?= (float)$expense['amount'] ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="expense_date" class="form-label fw-bold small">Ngày Chi Tiền <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?= e($expense['expense_date']) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="recipient" class="form-label fw-bold small">Người / Đơn Vị Nhận Tiền</label>
                            <input type="text" class="form-control" id="recipient" name="recipient" value="<?= e($expense['recipient'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold small">Chi Tiết / Ghi Chú</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= e($expense['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/expenses') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Cập Nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
