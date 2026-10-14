<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-tools text-primary me-2"></i>Yêu Cầu Sửa Chữa & Bảo Trì</h4>
        <span class="text-muted small">Theo dõi tiến độ xử lý sự cố trang thiết bị phòng trọ của bạn</span>
    </div>
    <a href="<?= url('/tenant/maintenance/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Báo Sự Cố Mới
    </a>
</div>

<div class="row g-4">
    <?php if (empty($requests)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
            <h5 class="text-muted">Mọi thiết bị trong phòng đều đang hoạt động tốt</h5>
            <p class="text-muted small">Nếu có sự cố hư hỏng (điện, nước, điều hòa, khóa cửa...), bạn có thể gửi yêu cầu hỗ trợ bất kỳ lúc nào.</p>
            <a href="<?= url('/tenant/maintenance/create') ?>" class="btn btn-primary btn-sm mt-2">Gửi yêu cầu sửa chữa</a>
        </div>
    <?php else: ?>
        <?php foreach ($requests as $r): ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-start border-4 border-<?= match($r['status']) { 'completed' => 'success', 'in_progress' => 'info', 'rejected' => 'secondary', default => 'warning' } ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-dark border font-monospace"><?= e($r['request_code']) ?></span>
                            <div>
                                <span class="badge bg-<?= match($r['priority']) { 'urgent' => 'danger', 'high' => 'warning text-dark', 'medium' => 'info text-dark', default => 'secondary' } ?> me-1">
                                    <?= match($r['priority']) { 'urgent' => 'Khẩn cấp', 'high' => 'Ưu tiên cao', 'medium' => 'Trung bình', default => 'Thấp' } ?>
                                </span>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Chờ tiếp nhận</span>
                                <?php elseif ($r['status'] === 'in_progress'): ?>
                                    <span class="badge bg-info text-dark">Đang xử lý</span>
                                <?php elseif ($r['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Đã hoàn thành</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Từ chối</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-2"><?= e($r['title']) ?></h5>
                        <p class="text-secondary small mb-3" style="white-space: pre-line; line-height: 1.6;"><?= e($r['description']) ?></p>

                        <?php if (!empty($r['image_path'])): ?>
                            <div class="mb-3">
                                <a href="<?= upload_url($r['image_path']) ?>" target="_blank">
                                    <img src="<?= upload_url($r['image_path']) ?>" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;" alt="Ảnh sự cố">
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($r['admin_notes'])): ?>
                            <div class="bg-light p-3 rounded small border mb-2">
                                <strong class="text-primary"><i class="bi bi-chat-left-dots me-1"></i> Phản hồi từ chủ trọ:</strong>
                                <div class="text-muted mt-1"><?= e($r['admin_notes']) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="text-muted small pt-2 border-top">
                            <i class="bi bi-clock me-1"></i> Gửi lúc: <?= format_datetime($r['created_at']) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
