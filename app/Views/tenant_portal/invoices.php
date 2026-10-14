<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Hóa Đơn Tiền Phòng Hằng Tháng</h4>
        <span class="text-muted small">Danh sách các kỳ hóa đơn và tình trạng thanh toán của bạn</span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Hóa Đơn</th>
                    <th>Kỳ Tháng</th>
                    <th>Tổng Tiền</th>
                    <th>Đã Trả</th>
                    <th>Còn Nợ</th>
                    <th>Hạn Trả</th>
                    <th>Trạng Thái</th>
                    <th class="text-end" style="width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            Bạn chưa có hóa đơn tiền phòng nào.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/tenant/invoices/show/' . $inv['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= e($inv['invoice_code']) ?>
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($inv['billing_month']) ?></span></td>
                            <td class="fw-bold text-dark"><?= format_currency($inv['total_amount']) ?></td>
                            <td class="text-success"><?= format_currency($inv['paid_amount']) ?></td>
                            <td>
                                <?php if ($inv['remaining_amount'] > 0): ?>
                                    <span class="text-danger fw-bold"><?= format_currency($inv['remaining_amount']) ?></span>
                                <?php else: ?>
                                    <span class="text-success fw-medium">0 đ</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= format_date($inv['due_date']) ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success badge-status"><i class="bi bi-check-circle me-1"></i> Đã thanh toán</span>
                                <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                    <span class="badge bg-info text-dark badge-status">Trả 1 phần</span>
                                <?php elseif ($inv['status'] === 'overdue'): ?>
                                    <span class="badge bg-danger badge-status">Quá hạn</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark badge-status">Chưa thanh toán</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/tenant/invoices/show/' . $inv['id']) ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-qr-code me-1"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
