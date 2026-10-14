<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Quản Lý Khu Trọ & Dãy Trọ</h4>
        <span class="text-muted small">Danh sách các khu nhà trọ, dãy phòng trọ thuộc hệ thống</span>
    </div>
    <a href="<?= url('/areas/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm Khu Trọ Mới
    </a>
</div>

<div class="row g-4">
    <?php if (empty($areas)): ?>
        <div class="col-12 text-center py-5">
            <div class="text-muted mb-3"><i class="bi bi-buildings fs-1"></i></div>
            <h5 class="text-muted">Chưa có khu trọ nào được thêm</h5>
            <a href="<?= url('/areas/create') ?>" class="btn btn-primary mt-2">Thêm khu trọ đầu tiên</a>
        </div>
    <?php else: ?>
        <?php foreach ($areas as $a): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-top border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1"><?= e($a['area_code']) ?></span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0 p-1" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="<?= url('/areas/edit/' . $a['id']) ?>"><i class="bi bi-pencil me-2 text-primary"></i> Sửa Thông Tin</a></li>
                                    <li><a class="dropdown-item" href="<?= url('/rooms?area_id=' . $a['id']) ?>"><i class="bi bi-door-open me-2 text-success"></i> Xem Danh Sách Phòng</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="<?= url('/areas/delete/' . $a['id']) ?>" method="POST" class="form-delete-confirm" data-confirm="Bạn có chắc chắn muốn xóa khu trọ <?= e($a['name']) ?> không?">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Xóa Khu Trọ</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <h5 class="card-title fw-bold text-dark mb-2"><?= e($a['name']) ?></h5>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($a['address']) ?>
                        </p>

                        <?php if (!empty($a['contact_phone'])): ?>
                            <p class="small text-secondary mb-3">
                                <i class="bi bi-telephone me-1 text-primary"></i> Hotline liên hệ: <strong><?= e($a['contact_phone']) ?></strong>
                            </p>
                        <?php endif; ?>

                        <div class="bg-light p-3 rounded-3 mb-3">
                            <div class="row text-center g-2 small">
                                <div class="col-4">
                                    <div class="text-muted" style="font-size: 0.75rem;">TỔNG SỐ</div>
                                    <strong class="fs-6 text-dark"><?= $a['total_rooms'] ?> phòng</strong>
                                </div>
                                <div class="col-4 border-start border-end">
                                    <div class="text-muted" style="font-size: 0.75rem;">ĐANG THUÊ</div>
                                    <strong class="fs-6 text-success"><?= $a['rented_rooms'] ?></strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted" style="font-size: 0.75rem;">CÒN TRỐNG</div>
                                    <strong class="fs-6 text-primary"><?= $a['empty_rooms'] ?></strong>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($a['description'])): ?>
                            <p class="small text-muted mb-0 text-truncate" title="<?= e($a['description']) ?>">
                                <i class="bi bi-info-circle me-1"></i> <?= e($a['description']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-3">
                        <a href="<?= url('/rooms?area_id=' . $a['id']) ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-arrow-right-circle me-1"></i> Quản lý phòng thuộc khu này
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
