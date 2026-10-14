<?php

/**
 * @var array $notifications
 * @var array $tenants
 */
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bell-fill text-primary me-2"></i>Quản Lý Thông Báo Cư Dân</h4>
        <span class="text-muted small">Đăng tin thông báo chung hoặc gửi tin nhắn riêng cho từng phòng trọ</span>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNotifModal">
        <i class="bi bi-megaphone-fill"></i> Đăng Thông Báo Mới
    </button>
</div>

<div class="row g-4">
    <?php if (empty($notifications)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-bell-slash fs-1 text-muted d-block mb-2"></i>
            <h5 class="text-muted">Chưa có thông báo nào được đăng</h5>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-start border-4 border-<?= match ($n['type']) {
                                                                                    'payment' => 'warning',
                                                                                    'maintenance' => 'info',
                                                                                    'system' => 'danger',
                                                                                    default => 'primary'
                                                                                } ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-<?= match ($n['type']) {
                                                        'payment' => 'warning text-dark',
                                                        'maintenance' => 'info text-dark',
                                                        'system' => 'danger',
                                                        default => 'primary'
                                                    } ?>">
                                <?= match ($n['type']) {
                                    'payment' => 'Tiền phòng & Phí',
                                    'maintenance' => 'Bảo trì & Vệ sinh',
                                    'system' => 'Khẩn cấp',
                                    default => 'Thông báo chung'
                                } ?>
                            </span>
                            <span class="small text-muted"><?= format_datetime($n['created_at']) ?></span>
                        </div>

                        <h5 class="fw-bold text-dark mb-2"><?= e($n['title']) ?></h5>
                        <p class="text-secondary small mb-3" style="white-space: pre-line; line-height: 1.6;"><?= e($n['content']) ?></p>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted">
                            <div>
                                <i class="bi bi-send me-1"></i> Gửi đến:
                                <?php if (!empty($n['target_tenant_name'])): ?>
                                    <strong class="text-primary"><?= e($n['target_tenant_name']) ?></strong>
                                <?php else: ?>
                                    <strong class="text-success">Toàn bộ khách thuê</strong>
                                <?php endif; ?>
                            </div>
                            <form action="<?= url('/notifications/delete/' . $n['id']) ?>" method="POST" class="form-delete-confirm" data-confirm="Xóa thông báo này?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Tạo Thông Báo Mới -->
<div class="modal fade" id="createNotifModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= url('/notifications') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-megaphone-fill text-primary me-2"></i>Đăng Thông Báo Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Tiêu Đề Thông Báo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="Ví dụ: Lịch thu tiền phòng tháng 10, Bảo dưỡng máy bơm..." required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Phân Loại Thông Báo</label>
                            <select name="type" class="form-select">
                                <option value="general" selected>Thông báo chung</option>
                                <option value="payment">Tiền phòng & Hóa đơn</option>
                                <option value="maintenance">Sửa chữa & Cắt nước</option>
                                <option value="system">Khẩn cấp</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Đối Tượng Nhận</label>
                            <select name="target_tenant_id" class="form-select">
                                <option value="">-- Gửi cho TẤT CẢ khách thuê --</option>
                                <?php foreach ($tenants as $t): ?>
                                    <option value="<?= $t['id'] ?>">Gửi riêng: <?= e($t['fullname']) ?> (SĐT: <?= e($t['phone']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Nội Dung Chi Tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="4" placeholder="Nhập nội dung thông báo gửi tới cư dân..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-1"></i> Phát Hành Thông Báo</button>
                </div>
            </form>
        </div>
    </div>
</div>