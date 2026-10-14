<?php

/**
 * @var array $contract
 */
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-tools text-primary me-2"></i>Gửi Yêu Cầu Sửa Chữa / Báo Hỏng</h4>
                <span class="text-muted small">Ban quản lý sẽ tiếp nhận và cử thợ qua kiểm tra khắc phục sớm nhất</span>
            </div>
            <a href="<?= url('/tenant/maintenance') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/tenant/maintenance') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="alert alert-light border small mb-4">
                        <i class="bi bi-house-door text-primary me-1"></i> Báo sự cố cho phòng: <strong><?= e($contract['room_code']) ?> (<?= e($contract['area_name']) ?>)</strong>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label fw-bold small">Tiêu Đề Sự Cố <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Ví dụ: Rò rỉ vòi nước, Điều hòa không mát, Đèn phòng bị cháy..." required autofocus>
                        </div>

                        <div class="col-md-4">
                            <label for="priority" class="form-label fw-bold small">Mức Độ Cần Thiết</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="low">Thấp (Chưa gấp)</option>
                                <option value="medium" selected>Trung bình</option>
                                <option value="high">Ưu tiên cao</option>
                                <option value="urgent">Khẩn cấp (Cúp điện, vỡ ống nước)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold small">Mô Tả Chi Tiết Vấn Đề <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Mô tả cụ thể vị trí hư hỏng, hiện tượng, thời điểm phát hiện và thời gian thợ có thể qua kiểm tra..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label for="image" class="form-label fw-bold small">Hình Ảnh Chụp Sự Cố (Chụp từ điện thoại)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" data-preview-target="maintPreviewContainer">
                            <div class="form-text">Chụp trực tiếp bằng camera điện thoại hoặc chọn ảnh từ thư viện để thợ dễ chuẩn bị đồ thay thế.</div>
                            <div id="maintPreviewContainer" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/tenant/maintenance') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-1"></i> Gửi Yêu Cầu Ngay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>