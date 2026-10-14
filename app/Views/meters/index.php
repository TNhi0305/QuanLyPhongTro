<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quản Lý Chỉ Số Điện & Nước</h4>
        <span class="text-muted small">Ghi nhận và đối soát số điện, số nước tiêu thụ định kỳ hằng tháng</span>
    </div>
    <a href="<?= url('/meters/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Ghi Số Điện Nước Mới
    </a>
</div>

<!-- Bộ lọc tháng và khu trọ -->
<div class="card mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="<?= url('/meters') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light fw-bold small">Kỳ Tính Tiền:</span>
                    <input type="month" class="form-control" name="billing_month" value="<?= e($billingMonth) ?>">
                </div>
            </div>
            <div class="col-md-5">
                <select name="area_id" class="form-select">
                    <option value="">-- Tất cả khu trọ --</option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $areaId == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Xem</button>
                <a href="<?= url('/meters') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng chỉ số điện nước -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Phòng / Khu Trọ</th>
                    <th>Khách Thuê</th>
                    <th>Chỉ Số Điện (Số Cũ &rarr; Mới)</th>
                    <th>Điện Tiêu Thụ</th>
                    <th>Chỉ Số Nước (Số Cũ &rarr; Mới)</th>
                    <th>Nước Tiêu Thụ</th>
                    <th>Ngày Ghi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($readings)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-speedometer2 fs-1 d-block mb-2"></i>
                            Chưa có dữ liệu ghi điện nước cho kỳ <?= e($billingMonth) ?>.
                            <div class="mt-2">
                                <a href="<?= url('/meters/create') ?>" class="btn btn-sm btn-primary">Ghi chỉ số ngay</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($readings as $m): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= e($m['room_code']) ?></div>
                                <span class="text-muted small"><?= e($m['area_name']) ?></span>
                            </td>
                            <td>
                                <?= !empty($m['tenant_name']) ? e($m['tenant_name']) : '<span class="text-muted small">Phòng trống</span>' ?>
                            </td>
                            <td>
                                <span class="font-monospace fw-semibold"><?= (int)$m['electricity_old'] ?></span>
                                &rarr;
                                <span class="font-monospace fw-bold text-primary"><?= (int)$m['electricity_new'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-2 py-1 fs-6">
                                    <i class="bi bi-lightning-fill text-warning me-1"></i> <?= (int)$m['electricity_usage'] ?> kWh
                                </span>
                            </td>
                            <td>
                                <span class="font-monospace fw-semibold"><?= (int)$m['water_old'] ?></span>
                                &rarr;
                                <span class="font-monospace fw-bold text-info"><?= (int)$m['water_new'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 fs-6">
                                    <i class="bi bi-droplet-fill me-1"></i> <?= (int)$m['water_usage'] ?> m³
                                </span>
                            </td>
                            <td class="small text-muted">
                                <?= format_date($m['recorded_date']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
