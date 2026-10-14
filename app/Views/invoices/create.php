<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Lập Hóa Đơn Tiền Phòng Thủ Công</h4>
                <span class="text-muted small">Tùy biến các khoản tiền phòng, điện nước và phí dịch vụ cho từng hợp đồng</span>
            </div>
            <a href="<?= url('/invoices') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/invoices') ?>" method="POST" id="invoiceForm">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contract_id" class="form-label fw-bold small">Chọn Hợp Đồng / Phòng Thuê <span class="text-danger">*</span></label>
                            <select name="contract_id" id="contract_id" class="form-select" required onchange="onContractChange(this)">
                                <option value="">-- Chọn hợp đồng đang hiệu lực --</option>
                                <?php foreach ($contracts as $c): ?>
                                    <option value="<?= $c['id'] ?>" data-rent="<?= $c['rent_price'] ?>" data-room-id="<?= $c['room_id'] ?>">
                                        <?= e($c['contract_code']) ?> &bull; Phòng <?= e($c['room_code']) ?> (<?= e($c['tenant_name']) ?>) - <?= format_currency($c['rent_price']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="billing_month" class="form-label fw-bold small">Kỳ Tính Tiền <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="billing_month" name="billing_month" value="<?= date('Y-m') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="due_date" class="form-label fw-bold small">Hạn Thanh Toán <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-20') ?>" required>
                        </div>

                        <!-- 1. Tiền phòng -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary border-bottom pb-2">
                                <i class="bi bi-door-open me-1"></i> 1. Tiền Thuê Phòng
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label for="room_price" class="form-label fw-bold small">Giá Tiền Phòng (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" step="1000" class="form-control" id="room_price" name="room_price" placeholder="3200000" required oninput="calcTotal()">
                        </div>

                        <!-- 2. Điện và Nước -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-warning border-bottom pb-2">
                                <i class="bi bi-lightning-charge-fill me-1"></i> 2. Tiền Điện & Tiền Nước
                            </h6>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Số Điện Tiêu Thụ (kWh)</label>
                            <input type="number" class="form-control" id="electricity_usage" name="electricity_usage" value="0" oninput="calcTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Đơn Giá Điện (đ/kWh)</label>
                            <input type="number" class="form-control" id="electricity_rate" name="electricity_rate" value="<?= (float)$elecRate ?>" oninput="calcTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Khối Nước Tiêu Thụ (m³)</label>
                            <input type="number" class="form-control" id="water_usage" name="water_usage" value="0" oninput="calcTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Đơn Giá Nước (đ/m³)</label>
                            <input type="number" class="form-control" id="water_rate" name="water_rate" value="<?= (float)$waterRate ?>" oninput="calcTotal()">
                        </div>

                        <!-- 3. Phí dịch vụ cố định -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-info border-bottom pb-2">
                                <i class="bi bi-sliders me-1"></i> 3. Các Khoản Phí Dịch Vụ Cố Định
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="row g-2">
                                <?php foreach ($services as $s): ?>
                                    <?php if ($s['type'] === 'fixed'): ?>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 border rounded bg-light">
                                                <input class="form-check-input service-check" type="checkbox" id="svc_<?= $s['id'] ?>" name="service_items[<?= $s['id'] ?>][name]" value="<?= e($s['name']) ?>" data-price="<?= (float)$s['price'] ?>" checked onchange="calcTotal()">
                                                <label class="form-check-label small fw-bold" for="svc_<?= $s['id'] ?>">
                                                    <?= e($s['name']) ?>
                                                    <div class="text-primary fw-normal"><?= format_currency($s['price']) ?></div>
                                                </label>
                                                <input type="hidden" name="service_items[<?= $s['id'] ?>][amount]" value="<?= (float)$s['price'] ?>">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- 4. Chi phí khác & Giảm giá -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-secondary border-bottom pb-2">
                                <i class="bi bi-plus-slash-minus me-1"></i> 4. Khoản Thu Khác & Giảm Trừ
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <label for="other_fees" class="form-label fw-bold small">Khoản Thu Thêm Khác (VNĐ)</label>
                            <input type="number" step="1000" class="form-control" id="other_fees" name="other_fees" value="0" oninput="calcTotal()">
                            <input type="text" class="form-control form-control-sm mt-1" name="other_fees_note" placeholder="Lý do thu thêm (sửa khóa, đền bù vật tư...)">
                        </div>
                        <div class="col-md-6">
                            <label for="discount" class="form-label fw-bold small">Giảm Giá / Khuyến Mãi (VNĐ)</label>
                            <input type="number" step="1000" class="form-control" id="discount" name="discount" value="0" oninput="calcTotal()">
                            <input type="text" class="form-control form-control-sm mt-1" name="discount_note" placeholder="Lý do giảm trừ (hỗ trợ mùa dịch, khuyến mãi...)">
                        </div>

                        <!-- Ghi chú hóa đơn -->
                        <div class="col-12 mt-3">
                            <label for="notes" class="form-label fw-bold small">Ghi Chú Hóa Đơn</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Ghi chú hiển thị trên biên lai gửi cho khách..."></textarea>
                        </div>
                    </div>

                    <!-- TỔNG CỘNG HÓA ĐƠN -->
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 mt-4 border border-primary border-opacity-25 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">TỔNG CỘNG HÓA ĐƠN PHẢI THU</span>
                            <div class="text-muted small" id="formulaSummary">Tiền phòng + Tiền điện + Tiền nước + Dịch vụ</div>
                        </div>
                        <div class="text-sm-end">
                            <h3 class="fw-bold text-primary mb-0" id="totalDisplay">0 đ</h3>
                            <input type="hidden" name="service_amount" id="service_amount" value="0">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/invoices') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle-fill me-1"></i> Lưu & Phát Hành Hóa Đơn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function onContractChange(select) {
    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.rent) {
        document.getElementById('room_price').value = opt.dataset.rent;
        calcTotal();
    }
}

function calcTotal() {
    const roomPrice = parseFloat(document.getElementById('room_price').value) || 0;
    const elecUsage = parseFloat(document.getElementById('electricity_usage').value) || 0;
    const elecRate = parseFloat(document.getElementById('electricity_rate').value) || 0;
    const elecTotal = elecUsage * elecRate;

    const waterUsage = parseFloat(document.getElementById('water_usage').value) || 0;
    const waterRate = parseFloat(document.getElementById('water_rate').value) || 0;
    const waterTotal = waterUsage * waterRate;

    let serviceTotal = 0;
    document.querySelectorAll('.service-check:checked').forEach(cb => {
        serviceTotal += parseFloat(cb.dataset.price) || 0;
    });
    document.getElementById('service_amount').value = serviceTotal;

    const otherFees = parseFloat(document.getElementById('other_fees').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;

    const total = roomPrice + elecTotal + waterTotal + serviceTotal + otherFees - discount;
    document.getElementById('totalDisplay').innerText = formatVND(Math.max(0, total));
}

document.addEventListener('DOMContentLoaded', calcTotal);
</script>
