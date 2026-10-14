<?php
/** @var array|null $contract */
/** @var array $tenant */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-text-fill text-primary me-2"></i>Hợp Đồng Thuê Phòng Của Bạn</h4>
        <span class="text-muted small">Thời hạn cam kết, tiền đặt cọc và các điều khoản pháp lý</span>
    </div>
    <?php if ($contract): ?>
        <a href="<?= url('/contracts/print/' . $contract['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> In Bản Ký
        </a>
    <?php endif; ?>
</div>

<?php if (!$contract): ?>
    <div class="card shadow-sm text-center py-5">
        <i class="bi bi-file-earmark-x fs-1 text-muted mb-2"></i>
        <h5 class="text-muted">Bạn chưa có hợp đồng thuê phòng nào đang hoạt động</h5>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-info-circle text-primary me-2"></i>Chi Tiết Hợp Đồng: <?= e($contract['contract_code']) ?>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Trạng thái:</span>
                            <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i> Đang hiệu lực</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Phòng thuê:</span>
                            <span class="fw-bold text-dark fs-6"><?= e($contract['room_code']) ?> (<?= e($contract['room_number']) ?>)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Khu trọ:</span>
                            <span><?= e($contract['area_name']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Ngày bắt đầu:</span>
                            <strong><?= format_date($contract['start_date']) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Ngày hết hạn:</span>
                            <strong class="text-danger"><?= format_date($contract['end_date']) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Giá thuê phòng:</span>
                            <strong class="text-primary fs-6"><?= format_currency($contract['rent_price']) ?> / tháng</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-3">
                            <span class="text-muted">Tiền đặt cọc đã nộp:</span>
                            <strong class="text-success fs-6"><?= format_currency($contract['deposit_amount']) ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-shield-check text-primary me-2"></i>Nội Quy & Điều Khoản Cam Kết
                </div>
                <div class="card-body">
                    <p class="text-secondary small" style="white-space: pre-line; line-height: 1.8;">
                        <?= e($contract['terms'] ?? 'Không có ghi chú điều khoản bổ sung.') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
