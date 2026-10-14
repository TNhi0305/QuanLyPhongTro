<?php

/**
 * @var array $room
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-door-open-fill text-primary me-2"></i>Thông Tin Phòng Đang Thuê</h4>
        <span class="text-muted small">Chi tiết tiện ích, diện tích và hình ảnh phòng của bạn</span>
    </div>
</div>

<?php if (!$room): ?>
    <div class="card shadow-sm text-center py-5">
        <i class="bi bi-house-x fs-1 text-muted mb-2"></i>
        <h5 class="text-muted">Bạn hiện chưa có phòng trọ nào đang thuê</h5>
    </div>
<?php else: ?>
    <div class="row g-4">
        <!-- Ảnh phòng -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-images text-primary me-2"></i>Hình Ảnh Phòng
                </div>
                <div class="card-body">
                    <?php if (empty($room['images'])): ?>
                        <div class="bg-light rounded text-center py-5 text-muted">
                            <i class="bi bi-image fs-1 d-block mb-2"></i> Chưa có hình ảnh tải lên.
                        </div>
                    <?php else: ?>
                        <div id="roomCarousel" class="carousel slide rounded overflow-hidden shadow-sm mb-3" data-bs-ride="carousel">
                            <div class="carousel-inner" style="max-height: 360px;">
                                <?php foreach ($room['images'] as $idx => $img): ?>
                                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                        <img src="<?= upload_url($img['image_path']) ?>" class="d-block w-100 object-fit-cover" style="height: 360px;" alt="Ảnh phòng">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($room['images']) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <h6 class="fw-bold"><i class="bi bi-info-circle text-primary me-2"></i>Tiện Nghi Phòng:</h6>
                        <p class="text-secondary small mb-0"><?= nl2br(e($room['description'] ?? 'Không có ghi chú thêm.')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông số kỹ thuật -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-card-checklist text-primary me-2"></i>Quy Cách Phòng
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between py-2">
                            <span class="text-muted">Mã số phòng:</span>
                            <span class="fw-bold text-dark fs-6"><?= e($room['room_code']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2">
                            <span class="text-muted">Khu trọ:</span>
                            <span class="fw-bold"><?= e($room['area_name']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2">
                            <span class="text-muted">Địa chỉ:</span>
                            <span><?= e($room['area_address']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2">
                            <span class="text-muted">Diện tích:</span>
                            <span class="fw-bold"><?= (float)$room['area_size'] ?> m²</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between py-2">
                            <span class="text-muted">Giá thuê thỏa thuận:</span>
                            <span class="fw-bold text-primary fs-6"><?= format_currency($contract['rent_price'] ?? $room['price']) ?> / tháng</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Hotline hỗ trợ -->
            <div class="card shadow-sm bg-light border">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-telephone-outbound fs-5"></i>
                        </div>
                        <div>
                            <span class="text-muted small">Cần hỗ trợ khẩn cấp?</span>
                            <div class="fw-bold text-dark fs-6"><?= e(get_setting('hotline', '0901.234.567')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>