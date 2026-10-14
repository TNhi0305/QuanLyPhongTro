<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-plus-fill text-primary me-2"></i>Tạo Hợp Đồng Thuê Phòng</h4>
                <span class="text-muted small">Ký hợp đồng thuê mới và tự động chuyển phòng sang trạng thái Đang Thuê</span>
            </div>
            <a href="<?= url('/contracts') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/contracts') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="contract_code" class="form-label fw-bold small">Mã Hợp Đồng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" id="contract_code" name="contract_code" value="HD-<?= date('Y') ?>-<?= str_pad((string)rand(10, 999), 3, '0', STR_PAD_LEFT) ?>" required>
                        </div>

                        <div class="col-md-8">
                            <label for="room_id" class="form-label fw-bold small">Phòng Thuê (Chỉ hiển thị phòng trống) <span class="text-danger">*</span></label>
                            <select name="room_id" id="room_id" class="form-select" required onchange="autoFillRoomData(this)">
                                <option value="">-- Chọn phòng còn trống --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>" data-price="<?= $r['price'] ?>" data-deposit="<?= $r['deposit'] ?>">
                                        <?= e($r['room_code']) ?> - <?= e($r['room_number']) ?> (<?= e($r['area_name']) ?>) - Giá: <?= format_currency($r['price']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="tenant_id" class="form-label fw-bold small">Người Đứng Tên Thuê <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="tenant_id" id="tenant_id" class="form-select" required>
                                    <option value="">-- Chọn khách thuê --</option>
                                    <?php foreach ($tenants as $t): ?>
                                        <option value="<?= $t['id'] ?>">
                                            <?= e($t['fullname']) ?> - SĐT: <?= e($t['phone']) ?> <?= !empty($t['id_card']) ? '(CCCD: ' . e($t['id_card']) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="<?= url('/tenants/create') ?>" target="_blank" class="btn btn-outline-primary" title="Thêm khách mới nếu chưa có">
                                    <i class="bi bi-plus-lg"></i> Thêm khách mới
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-bold small">Ngày Bắt Đầu Hợp Đồng <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-01') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-bold small">Ngày Kết Thúc Hợp Đồng <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= date('Y-m-d', strtotime('+1 year -1 day')) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label for="rent_price" class="form-label fw-bold small">Giá Thuê Đã Thỏa Thuận (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" step="10000" class="form-control" id="rent_price" name="rent_price" placeholder="3200000" required>
                        </div>

                        <div class="col-md-4">
                            <label for="deposit_amount" class="form-label fw-bold small">Tiền Đặt Cọc (VNĐ)</label>
                            <input type="number" step="10000" class="form-control" id="deposit_amount" name="deposit_amount" placeholder="3200000">
                        </div>

                        <div class="col-md-4">
                            <label for="payment_cycle" class="form-label fw-bold small">Chu Kỳ Thanh Toán (Tháng)</label>
                            <select name="payment_cycle" id="payment_cycle" class="form-select">
                                <option value="1" selected>Hằng tháng (1 tháng / lần)</option>
                                <option value="2">2 tháng / lần</option>
                                <option value="3">3 tháng / lần</option>
                                <option value="6">6 tháng / lần</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="terms" class="form-label fw-bold small">Điều Khoản Hợp Đồng & Nội Quy Phòng Trọ</label>
                            <textarea class="form-control" id="terms" name="terms" rows="4">- Bên B cam kết thanh toán tiền phòng và các chi phí điện, nước đúng thời hạn từ ngày 01 đến ngày 05 hàng tháng.
- Giữ gìn an ninh trật tự chung, không tụ tập làm ồn sau 23h00 đêm.
- Tự bảo quản tài sản cá nhân và giữ gìn các trang thiết bị nội thất trong phòng.
- Báo trước ít nhất 30 ngày cho Bên A trước khi có ý định trả phòng để được hoàn lại tiền đặt cọc.</textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/contracts') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill me-1"></i> Xác Nhận Tạo Hợp Đồng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function autoFillRoomData(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.dataset.price) {
        document.getElementById('rent_price').value = selectedOption.dataset.price;
        document.getElementById('deposit_amount').value = selectedOption.dataset.deposit || selectedOption.dataset.price;
    }
}
</script>
