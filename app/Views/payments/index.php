<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Lịch Sử Thu Tiền & Phiếu Thu</h4>
        <span class="text-muted small">Danh sách các giao dịch thanh toán tiền phòng và dịch vụ đã thu</span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Phiếu Thu</th>
                    <th>Hóa Đơn / Kỳ</th>
                    <th>Phòng / Khách Thuê</th>
                    <th>Thời Gian Thu</th>
                    <th>Hình Thức</th>
                    <th>Số Tiền Đã Thu</th>
                    <th>Mã Tham Chiếu / Ghi Chú</th>
                    <th>Người Lập</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                            Chưa có giao dịch thu tiền nào được ghi nhận.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace fs-6"><?= e($p['payment_code']) ?></span>
                            </td>
                            <td>
                                <a href="<?= url('/invoices/show/' . $p['invoice_id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= e($p['invoice_code']) ?>
                                </a>
                                <div class="text-muted small">Kỳ <?= e($p['billing_month']) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($p['room_code']) ?></div>
                                <span class="text-muted small"><?= e($p['tenant_name']) ?></span>
                            </td>
                            <td class="small">
                                <?= format_datetime($p['payment_date']) ?>
                            </td>
                            <td>
                                <?php if ($p['payment_method'] === 'bank_transfer'): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-bank me-1"></i> Chuyển khoản
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-cash me-1"></i> Tiền mặt
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-bold text-success fs-6">+ <?= format_currency($p['amount']) ?></span>
                            </td>
                            <td class="small text-muted">
                                <?= e($p['transaction_ref'] ?? $p['notes'] ?? '-') ?>
                            </td>
                            <td class="small">
                                <?= e($p['created_by_name'] ?? 'Quản trị viên') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
