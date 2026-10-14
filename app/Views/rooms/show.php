<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Chi Tiết Phòng: <?= e($room['room_code']) ?> - <?= e($room['room_number']) ?></h4>
            <?php if ($room['status'] === 'empty'): ?>
                <span class="badge bg-secondary">Trống</span>
            <?php elseif ($room['status'] === 'rented'): ?>
                <span class="badge bg-success">Đang thuê</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">Bảo trì</span>
            <?php endif; ?>
        </div>
        <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?= e($room['area_name']) ?> &bull; <?= e($room['area_address']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/rooms/edit/' . $room['id']) ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil"></i> Sửa Phòng
        </a>
        <a href="<?= url('/rooms') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Danh Sách
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Hình ảnh phòng -->
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-images text-primary me-2"></i>Hình Ảnh Thực Tế
            </div>
            <div class="card-body">
                <?php if (empty($room['images'])): ?>
                    <div class="bg-light rounded text-center py-5 text-muted">
                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                        Chưa có hình ảnh nào được tải lên cho phòng này.
                    </div>
                <?php else: ?>
                    <!-- Carousel hiển thị ảnh -->
                    <div id="roomCarousel" class="carousel slide rounded overflow-hidden shadow-sm mb-3" data-bs-ride="carousel">
                        <div class="carousel-inner" style="max-height: 380px;">
                            <?php foreach ($room['images'] as $idx => $img): ?>
                                <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                    <img src="<?= upload_url($img['image_path']) ?>" class="d-block w-100 object-fit-cover" style="height: 380px;" alt="Room Image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($room['images']) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Thumbnails thu nhỏ -->
                    <div class="d-flex gap-2 overflow-x-auto pb-2">
                        <?php foreach ($room['images'] as $idx => $img): ?>
                            <img src="<?= upload_url($img['image_path']) ?>" class="rounded object-fit-cover border" style="width: 70px; height: 60px; cursor: pointer;" onclick="bootstrap.Carousel.getInstance(document.getElementById('roomCarousel')).to(<?= $idx ?>)">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <h6 class="fw-bold text-dark"><i class="bi bi-card-text text-primary me-2"></i>Mô Tả & Tiện Nghi:</h6>
                    <p class="text-secondary"><?= nl2br(e($room['description'] ?? 'Chưa có thông tin mô tả chi tiết.')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông số phòng & Hợp đồng hiện tại -->
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-info-circle text-primary me-2"></i>Thông Tin Chi Tiết
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Mã phòng:</span>
                        <span class="fw-bold text-dark"><?= e($room['room_code']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Giá thuê thỏa thuận:</span>
                        <span class="fw-bold text-primary fs-6"><?= format_currency($room['price']) ?> / tháng</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Tiền đặt cọc:</span>
                        <span class="fw-bold"><?= format_currency($room['deposit']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Diện tích phòng:</span>
                        <span class="fw-bold"><?= (float)$room['area_size'] ?> m²</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Số người ở tối đa:</span>
                        <span class="fw-bold"><?= (int)$room['max_occupants'] ?> người</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Khách thuê & Hợp đồng hiện tại -->
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-person-badge text-primary me-2"></i>Khách Thuê Hiện Tại
            </div>
            <div class="card-body">
                <?php if (!empty($room['current_contract_id'])): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= e($room['tenant_name']) ?></h6>
                            <span class="text-muted small"><i class="bi bi-telephone"></i> <?= e($room['tenant_phone']) ?></span>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Mã hợp đồng:</span>
                            <strong><?= e($room['contract_code']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Thời hạn:</span>
                            <span><?= format_date($room['contract_start']) ?> &rarr; <?= format_date($room['contract_end']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Giá thuê ký kết:</span>
                            <strong class="text-success"><?= format_currency($room['agreed_rent']) ?></strong>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= url('/contracts/show/' . $room['current_contract_id']) ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                            <i class="bi bi-file-text"></i> Xem Hợp Đồng
                        </a>
                        <a href="<?= url('/tenants/show/' . $room['tenant_id']) ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-person"></i> Hồ Sơ Khách
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted small mb-3">Phòng này hiện tại chưa có hợp đồng thuê hoạt động.</p>
                        <a href="<?= url('/contracts/create') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-plus"></i> Lập Hợp Đồng Cho Thuê Ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
