<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Ghi Chỉ Số Điện & Nước</h4>
                <span class="text-muted small">Nhập chỉ số công tơ điện và đồng hồ nước định kỳ cho từng phòng</span>
            </div>
            <a href="<?= url('/meters') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/meters') ?>" method="POST" id="meterForm">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="room_id" class="form-label fw-bold small">Chọn Phòng Trọ <span class="text-danger">*</span></label>
                            <select name="room_id" id="room_id" class="form-select" required onchange="fetchPreviousReading()">
                                <option value="">-- Chọn phòng --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['id'] ?>">
                                        <?= e($r['room_code']) ?> - <?= e($r['room_number']) ?> (<?= e($r['area_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="billing_month" class="form-label fw-bold small">Kỳ Tính Tiền (Tháng/Năm) <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="billing_month" name="billing_month" value="<?= e($defaultMonth) ?>" required onchange="fetchPreviousReading()">
                        </div>

                        <!-- CHỈ SỐ ĐIỆN -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-warning border-bottom pb-2">
                                <i class="bi bi-lightning-charge-fill me-1"></i> 1. Chỉ Số Công Tơ Điện (kWh)
                            </h6>
                        </div>

                        <div class="col-md-4">
                            <label for="electricity_old" class="form-label fw-bold small">Số Điện Cũ (Tháng trước)</label>
                            <input type="number" class="form-control" id="electricity_old" name="electricity_old" value="0" required oninput="calculateUsage()">
                        </div>

                        <div class="col-md-4">
                            <label for="electricity_new" class="form-label fw-bold small">Số Điện Mới (Tháng này) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="electricity_new" name="electricity_new" required oninput="calculateUsage()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Số Điện Tiêu Thụ</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light fw-bold text-primary" id="electricity_usage_display" value="0" readonly>
                                <span class="input-group-text bg-light">kWh</span>
                            </div>
                        </div>

                        <!-- CHỈ SỐ NƯỚC -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-info border-bottom pb-2">
                                <i class="bi bi-droplet-fill me-1"></i> 2. Chỉ Số Đồng Hồ Nước (m³)
                            </h6>
                        </div>

                        <div class="col-md-4">
                            <label for="water_old" class="form-label fw-bold small">Số Nước Cũ (Tháng trước)</label>
                            <input type="number" class="form-control" id="water_old" name="water_old" value="0" required oninput="calculateUsage()">
                        </div>

                        <div class="col-md-4">
                            <label for="water_new" class="form-label fw-bold small">Số Nước Mới (Tháng này) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="water_new" name="water_new" required oninput="calculateUsage()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Khối Nước Tiêu Thụ</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light fw-bold text-info" id="water_usage_display" value="0" readonly>
                                <span class="input-group-text bg-light">m³</span>
                            </div>
                        </div>

                        <!-- THÔNG TIN THÊM -->
                        <div class="col-md-6 mt-3">
                            <label for="recorded_date" class="form-label fw-bold small">Ngày Chốt Số</label>
                            <input type="date" class="form-control" id="recorded_date" name="recorded_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label for="notes" class="form-label fw-bold small">Ghi Chú</label>
                            <input type="text" class="form-control" id="notes" name="notes" placeholder="Ghi chú thêm nếu có...">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/meters') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-lg me-1"></i> Lưu Chỉ Số Điện Nước
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculateUsage() {
    const eOld = parseInt(document.getElementById('electricity_old').value) || 0;
    const eNew = parseInt(document.getElementById('electricity_new').value) || 0;
    const eUsage = Math.max(0, eNew - eOld);
    document.getElementById('electricity_usage_display').value = eUsage;

    const wOld = parseInt(document.getElementById('water_old').value) || 0;
    const wNew = parseInt(document.getElementById('water_new').value) || 0;
    const wUsage = Math.max(0, wNew - wOld);
    document.getElementById('water_usage_display').value = wUsage;

    // Cảnh báo nếu số mới nhỏ hơn số cũ
    const eInput = document.getElementById('electricity_new');
    if (eNew > 0 && eNew < eOld) {
        eInput.classList.add('is-invalid');
    } else {
        eInput.classList.remove('is-invalid');
    }

    const wInput = document.getElementById('water_new');
    if (wNew > 0 && wNew < wOld) {
        wInput.classList.add('is-invalid');
    } else {
        wInput.classList.remove('is-invalid');
    }
}

function fetchPreviousReading() {
    const roomId = document.getElementById('room_id').value;
    const month = document.getElementById('billing_month').value;
    if (!roomId || !month) return;

    fetch('<?= url('/meters/prev-reading-ajax') ?>?room_id=' + roomId + '&billing_month=' + month)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('electricity_old').value = data.electricity_old;
                document.getElementById('water_old').value = data.water_old;
                if (data.is_existing) {
                    document.getElementById('electricity_new').value = data.electricity_new;
                    document.getElementById('water_new').value = data.water_new;
                }
                calculateUsage();
            }
        })
        .catch(err => console.error('Error fetching meter reading:', err));
}
</script>
