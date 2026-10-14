<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-speedometer text-warning me-2"></i>Chỉ Số Điện & Nước Của Phòng</h4>
        <span class="text-muted small">Lịch sử ghi số công tơ điện và đồng hồ nước định kỳ qua các tháng</span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Kỳ Tháng</th>
                    <th>Chỉ Số Điện (Cũ &rarr; Mới)</th>
                    <th>Điện Tiêu Thụ</th>
                    <th>Chỉ Số Nước (Cũ &rarr; Mới)</th>
                    <th>Nước Tiêu Thụ</th>
                    <th>Ngày Chốt Số</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($readings)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-lightning-charge fs-1 d-block mb-2"></i>
                            Chưa có dữ liệu ghi chỉ số điện nước cho phòng của bạn.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($readings as $m): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border font-monospace fs-6"><?= e($m['billing_month']) ?></span></td>
                            <td>
                                <span class="font-monospace"><?= (int)$m['electricity_old'] ?></span> &rarr; 
                                <span class="font-monospace fw-bold text-primary"><?= (int)$m['electricity_new'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-2 py-1 fs-6">
                                    <i class="bi bi-lightning-fill text-warning me-1"></i> <?= (int)$m['electricity_usage'] ?> kWh
                                </span>
                            </td>
                            <td>
                                <span class="font-monospace"><?= (int)$m['water_old'] ?></span> &rarr; 
                                <span class="font-monospace fw-bold text-info"><?= (int)$m['water_new'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 fs-6">
                                    <i class="bi bi-droplet-fill me-1"></i> <?= (int)$m['water_usage'] ?> m³
                                </span>
                            </td>
                            <td class="small text-muted"><?= format_date($m['recorded_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
