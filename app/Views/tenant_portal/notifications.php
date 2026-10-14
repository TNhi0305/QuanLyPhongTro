<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bell-fill text-warning me-2"></i>Bảng Tin Thông Báo</h4>
        <span class="text-muted small">Thông báo và tin tức từ ban quản lý khu trọ gửi đến bạn</span>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($notifications)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-bell-slash fs-1 text-muted d-block mb-2"></i>
            <h5 class="text-muted">Chưa có thông báo nào</h5>
            <p class="text-muted small">Các thông báo về lịch thu tiền, vệ sinh, bảo trì định kỳ sẽ xuất hiện tại đây.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-start border-4 border-<?= match($n['type']) { 'payment' => 'warning', 'maintenance' => 'info', 'system' => 'danger', default => 'primary' } ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-<?= match($n['type']) { 'payment' => 'warning text-dark', 'maintenance' => 'info text-dark', 'system' => 'danger', default => 'primary' } ?>">
                                <?= match($n['type']) { 'payment' => 'Tiền phòng', 'maintenance' => 'Bảo trì', 'system' => 'Khẩn cấp', default => 'Chung' } ?>
                            </span>
                            <span class="small text-muted"><?= format_datetime($n['created_at']) ?></span>
                        </div>

                        <h5 class="fw-bold text-dark mb-2"><?= e($n['title']) ?></h5>
                        <p class="text-secondary small mb-0" style="white-space: pre-line; line-height: 1.7;"><?= e($n['content']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
