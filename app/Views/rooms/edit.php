<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Sửa Phòng Trọ:
                    <?= e($room['room_code']) ?></h4>
                <span class="text-muted small">Cập nhật thông tin và quản lý hình ảnh phòng</span>
            </div>
            <a href="<?= url('/rooms/show/' . $room['id']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Xem Chi Tiết
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="<?= url('/rooms/update/' . $room['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="area_id" class="form-label fw-bold small">Khu Trọ / Dãy Trọ <span
                                    class="text-danger">*</span></label>
                            <select name="area_id" id="area_id" class="form-select" required>
                                <?php foreach ($areas as $a): ?>
                                <option value="<?= (int)$a['id'] ?>"
                                    <?= $room['area_id'] == $a['id'] ? 'selected' : '' ?>>
                                    <?= e($a['name']) ?> (<?= e($a['area_code']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="room_code" class="form-label fw-bold small">Mã Phòng <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="room_code" name="room_code"
                                value="<?= e($room['room_code']) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label fw-bold small">Trạng Thái</label>
                            <select name="status" id="status" class="form-select">
                                <option value="empty" <?= $room['status'] === 'empty' ? 'selected' : '' ?>>Trống
                                </option>
                                <option value="rented" <?= $room['status'] === 'rented' ? 'selected' : '' ?>>Đang thuê
                                </option>
                                <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>
                                    Đang bảo trì</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="room_number" class="form-label fw-bold small">Tên / Số Phòng <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="room_number" name="room_number"
                                value="<?= e($room['room_number']) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="area_size" class="form-label fw-bold small">Diện Tích (m²)</label>
                            <input type="number" step="0.1" min="0" class="form-control" id="area_size" name="area_size"
                                value="<?= (float)$room['area_size'] ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="max_occupants" class="form-label fw-bold small">Số Người Tối Đa</label>
                            <input type="number" min="1" class="form-control" id="max_occupants" name="max_occupants"
                                value="<?= (int)$room['max_occupants'] ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label fw-bold small">Giá Thuê / Tháng (VNĐ) <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="10000" min="1" class="form-control" id="price" name="price"
                                value="<?= (float)$room['price'] ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="deposit" class="form-label fw-bold small">Tiền Đặt Cọc (VNĐ)</label>
                            <input type="number" step="10000" min="0" class="form-control" id="deposit" name="deposit"
                                value="<?= (float)$room['deposit'] ?>">
                        </div>

                        <div class="col-12">
                            <label for="images" class="form-label fw-bold small">Tải Thêm Hình Ảnh Từ Máy Tính</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                data-preview-target="imagePreviewContainer">
                            <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <div class="col-12">
                            <label for="image_urls" class="form-label fw-bold small">Hoặc thêm ảnh bằng URL (Google /
                                Internet)</label>
                            <textarea class="form-control" id="image_urls" name="image_urls" rows="3"
                                placeholder="Dán URL ảnh trực tiếp, mỗi dòng một URL&#10;Ví dụ: https://example.com/phong-tro.jpg"></textarea>
                            <div class="form-text">Mỗi dòng một URL ảnh. Cần dùng địa chỉ ảnh trực tiếp (HTTP/HTTPS),
                                không phải đường dẫn trang kết quả Google. Ảnh có thể không hiển thị nếu website nguồn
                                chặn nhúng.</div>
                            <button type="button" id="previewImageUrlsBtn" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-eye me-1"></i> Xem trước URL ảnh
                            </button>
                            <div id="urlImagePreviewContainer" class="d-flex flex-wrap gap-2 mt-3"></div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold small">Mô Tả Tiện Nghi</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="3"><?= e($room['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/rooms') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Cập Nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-images text-primary me-2"></i>Thư Viện Ảnh Hiện Tại Của Phòng
                (<?= count($room['images'] ?? []) ?>)
            </div>
            <div class="card-body">
                <?php if (empty($room['images'])): ?>
                <p class="text-muted text-center py-3 mb-0">Chưa có hình ảnh nào cho phòng này.</p>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($room['images'] as $img): ?>
                    <?php
                                $storedPath = (string)($img['image_path'] ?? '');
                                $isExternal = filter_var($storedPath, FILTER_VALIDATE_URL)
                                    && in_array(strtolower((string)parse_url($storedPath, PHP_URL_SCHEME)), ['http', 'https'], true);
                                $imageSrc = $isExternal ? $storedPath : upload_url($storedPath);
                            ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border position-relative overflow-hidden">
                            <img src="<?= e($imageSrc) ?>" class="card-img-top object-fit-cover" style="height: 140px;"
                                alt="Hình ảnh phòng <?= e($room['room_code']) ?>" loading="lazy"
                                onerror="this.onerror=null;this.alt='Không thể tải ảnh';this.style.opacity='.35';">
                            <?php if (!empty($img['is_primary'])): ?>
                            <span class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm">Ảnh đại
                                diện</span>
                            <?php endif; ?>
                            <div class="card-body p-2 d-flex justify-content-between align-items-center bg-light">
                                <?php if (empty($img['is_primary'])): ?>
                                <form action="<?= url('/rooms/image/primary/' . (int)$img['id']) ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="room_id" value="<?= (int)$room['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success p-1"
                                        title="Đặt làm ảnh bìa">
                                        <i class="bi bi-star"></i> Chọn làm bìa
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="small text-success fw-bold"><i class="bi bi-star-fill"></i> Ảnh
                                    chính</span>
                                <?php endif; ?>

                                <form action="<?= url('/rooms/image/delete/' . (int)$img['id']) ?>" method="POST"
                                    class="form-delete-confirm" data-confirm="Xóa hình ảnh này?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="room_id" value="<?= (int)$room['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Xóa ảnh">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('image_urls');
    const urlPreview = document.getElementById('urlImagePreviewContainer');
    const previewButton = document.getElementById('previewImageUrlsBtn');
    const fileInput = document.getElementById('images');
    const filePreview = document.getElementById('imagePreviewContainer');

    function makePreviewCard(src, label, onRemove) {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded p-2 position-relative';
        wrapper.style.width = '150px';

        const image = document.createElement('img');
        image.src = src;
        image.alt = label;
        image.loading = 'lazy';
        image.style.width = '100%';
        image.style.height = '100px';
        image.style.objectFit = 'cover';
        image.className = 'rounded';
        image.onerror = function() {
            image.remove();
            const error = document.createElement('div');
            error.className = 'small text-danger py-3';
            error.textContent = 'Không tải được ảnh. Hãy kiểm tra URL.';
            wrapper.insertBefore(error, wrapper.firstChild);
        };

        const caption = document.createElement('div');
        caption.className = 'small text-truncate mt-1';
        caption.textContent = label;

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-outline-danger mt-2 w-100';
        removeButton.textContent = 'Bỏ khỏi danh sách';
        removeButton.addEventListener('click', onRemove);

        wrapper.appendChild(image);
        wrapper.appendChild(caption);
        wrapper.appendChild(removeButton);
        return wrapper;
    }

    function renderUrlPreviews() {
        urlPreview.replaceChildren();

        const urls = [...new Set(urlInput.value.split(/\r?\n/).map(v => v.trim()).filter(Boolean))];

        urls.forEach(function(value) {
            let parsed;
            try {
                parsed = new URL(value);
            } catch (_) {
                const error = document.createElement('div');
                error.className = 'alert alert-warning py-2 mb-0';
                error.textContent = 'URL không hợp lệ: ' + value;
                urlPreview.appendChild(error);
                return;
            }

            if (!['http:', 'https:'].includes(parsed.protocol)) {
                const error = document.createElement('div');
                error.className = 'alert alert-warning py-2 mb-0';
                error.textContent = 'Chỉ chấp nhận URL HTTP/HTTPS: ' + value;
                urlPreview.appendChild(error);
                return;
            }

            const remove = function() {
                urlInput.value = [...new Set(urlInput.value.split(/\r?\n/).map(v => v.trim())
                        .filter(Boolean))]
                    .filter(url => url !== value)
                    .join('\n');
                renderUrlPreviews();
            };

            urlPreview.appendChild(makePreviewCard(value, parsed.hostname, remove));
        });
    }

    previewButton.addEventListener('click', renderUrlPreviews);
    urlInput.addEventListener('change', renderUrlPreviews);

    fileInput.addEventListener('change', function() {
        filePreview.replaceChildren();
        Array.from(fileInput.files || []).forEach(function(file) {
            if (!file.type.startsWith('image/')) return;
            const objectUrl = URL.createObjectURL(file);
            const card = makePreviewCard(objectUrl, file.name, function() {
                // Không thể xóa riêng một file trong FileList ở mọi trình duyệt;
                // người dùng có thể chọn lại danh sách file.
                card.remove();
            });
            filePreview.appendChild(card);
        });
    });
});
</script>