<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Lịch Sử Thanh Toán Của Bạn</h4>
        <span class="text-muted small">Tất cả các giao dịch đóng tiền phòng và nộp phí dịch vụ đã được ghi nhận</span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Phiếu Thu</th>
                    <th>Hóa Đơn</th>
                    <th>Thời Gian Nộp</th>
                    <th>Phương Thức</th>
                    <th>Số Tiền Đã Đóng</th>
                    <th>Mã Tham Chiếu / Ghi Chú</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                            Bạn chưa có giao dịch đóng tiền nào được ghi nhận.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= e($p['payment_code']) ?></span></td>
                            <td>
                                <a href="<?= url('/tenant/invoices/show/' . $p['invoice_id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= e($p['invoice_code']) ?>
                                </a>
                                <span class="text-muted small"> (Kỳ <?= e($p['billing_month']) ?>)</span>
                            </td>
                            <td class="small text-muted"><?= format_datetime($p['payment_date']) ?></td>
                            <td>
                                <?php if ($p['payment_method'] === 'bank_transfer'): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary"><i class="bi bi-bank me-1"></i> Chuyển khoản</span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-cash me-1"></i> Tiền mặt</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="fw-bold text-success fs-6">+ <?= format_currency($p['amount']) ?></span></td>
                            <td class="small text-muted"><?= e($p['transaction_ref'] ?? $p['notes'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
