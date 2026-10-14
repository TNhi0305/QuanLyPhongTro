<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-door-open-fill text-primary me-2"></i>Thêm Phòng Trọ Mới</h4>
                <span class="text-muted small">Khai báo phòng trọ mới vào hệ thống</span>
            </div>
            <a href="<?= url('/rooms') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/rooms') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="area_id" class="form-label fw-bold small">Khu Trọ / Dãy Trọ <span class="text-danger">*</span></label>
                            <select name="area_id" id="area_id" class="form-select" required>
                                <option value="">-- Chọn khu trọ --</option>
                                <?php foreach ($areas as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= e($a['name']) ?> (<?= e($a['area_code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="room_code" class="form-label fw-bold small">Mã Phòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="room_code" name="room_code" placeholder="P101, P102..." required>
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label fw-bold small">Trạng Thái Ban Đầu</label>
                            <select name="status" id="status" class="form-select">
                                <option value="empty" selected>Trống</option>
                                <option value="maintenance">Đang bảo trì</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="room_number" class="form-label fw-bold small">Tên / Số Phòng Đầy Đủ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="room_number" name="room_number" placeholder="Ví dụ: Phòng 101 (Tầng 1)" required>
                        </div>

                        <div class="col-md-3">
                            <label for="area_size" class="form-label fw-bold small">Diện Tích (m²)</label>
                            <input type="number" step="0.1" class="form-control" id="area_size" name="area_size" value="22" placeholder="22.5">
                        </div>

                        <div class="col-md-3">
                            <label for="max_occupants" class="form-label fw-bold small">Số Người Tối Đa</label>
                            <input type="number" class="form-control" id="max_occupants" name="max_occupants" value="2" min="1" max="10">
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label fw-bold small">Giá Thuê / Tháng (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" step="10000" class="form-control" id="price" name="price" placeholder="Ví dụ: 3200000" required>
                        </div>

                        <div class="col-md-6">
                            <label for="deposit" class="form-label fw-bold small">Tiền Đặt Cọc Mặc Định (VNĐ)</label>
                            <input type="number" step="10000" class="form-control" id="deposit" name="deposit" placeholder="Ví dụ: 3200000">
                        </div>

                        <div class="col-12">
                            <label for="images" class="form-label fw-bold small">Hình Ảnh Phòng (Có thể chọn nhiều ảnh JPG, PNG, WEBP)</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" data-preview-target="imagePreviewContainer">
                            <div class="form-text">Bạn có thể chọn cùng lúc nhiều hình ảnh chụp thực tế của phòng.</div>
                            <div id="imagePreviewContainer" class="d-flex flex-wrap mt-2"></div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold small">Mô Tả Tiện Nghi Trong Phòng</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Mô tả nội thất, gác lửng, máy lạnh, tủ lạnh, bếp nấu ăn, nhà vệ sinh khép kín..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/rooms') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Phòng Trọ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
