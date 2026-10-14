<?php

/**
 * @var array $invoice
 */
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Hóa Đơn: <?= e($invoice['invoice_code']) ?></h4>
            <?php if ($invoice['status'] === 'paid'): ?>
                <span class="badge bg-success">Đã thanh toán</span>
            <?php elseif ($invoice['status'] === 'partially_paid'): ?>
                <span class="badge bg-info text-dark">Thanh toán 1 phần</span>
            <?php elseif ($invoice['status'] === 'overdue'): ?>
                <span class="badge bg-danger">Quá hạn</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">Chưa thanh toán</span>
            <?php endif; ?>
        </div>
        <span class="text-muted small">Kỳ: Tháng <strong><?= e($invoice['billing_month']) ?></strong> &bull; Hạn thanh toán: <?= format_date($invoice['due_date']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/invoices/print/' . $invoice['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> In Biên Lai
        </a>
        <a href="<?= url('/tenant/invoices') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Danh Sách
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Bảng kê các khoản tiền -->
    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-list-check text-primary me-2"></i>Chi Tiết Các Khoản Phí
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Khoản thu</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoice['details'])): ?>
                            <tr>
                                <td>Tiền phòng tháng <?= e($invoice['billing_month']) ?></td>
                                <td class="text-center">1 Tháng</td>
                                <td class="text-end"><?= format_currency($invoice['room_price']) ?></td>
                                <td class="text-end fw-bold"><?= format_currency($invoice['room_price']) ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoice['details'] as $d): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($d['item_name']) ?></div>
                                        <?php if (!empty($d['note'])): ?>
                                            <span class="text-muted small"><?= e($d['note']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (float)$d['quantity'] ?> <?= e($d['unit']) ?></td>
                                    <td class="text-end"><?= format_currency($d['unit_price']) ?></td>
                                    <td class="text-end fw-bold"><?= format_currency($d['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Tổng hóa đơn:</td>
                            <td class="text-end fw-bold fs-6"><?= format_currency($invoice['total_amount']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-success fw-bold">Đã thanh toán:</td>
                            <td class="text-end text-success fw-bold">- <?= format_currency($invoice['paid_amount']) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="3" class="text-end text-danger fw-bold fs-6">SỐ TIỀN CÒN LẠI:</td>
                            <td class="text-end text-danger fw-bold fs-5"><?= format_currency($invoice['remaining_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Lịch sử các lần nộp tiền -->
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-clock-history text-primary me-2"></i>Lịch Sử Thanh Toán
            </div>
            <div class="card-body p-0">
                <?php if (empty($invoice['payments'])): ?>
                    <p class="text-muted text-center py-4 mb-0 small">Chưa có giao dịch thanh toán nào được ghi nhận cho hóa đơn này.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($invoice['payments'] as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">
                                        <?= $p['payment_method'] === 'bank_transfer' ? 'Chuyển khoản ngân hàng' : 'Tiền mặt' ?>
                                    </div>
                                    <span class="text-muted"><?= format_datetime($p['payment_date']) ?> &bull; Mã: <?= e($p['payment_code']) ?></span>
                                    <?php if (!empty($p['transaction_ref'])): ?>
                                        <div class="text-muted">Tham chiếu: <?= e($p['transaction_ref']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="fw-bold text-success fs-6">+ <?= format_currency($p['amount']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cột quét mã VietQR và Hướng dẫn chuyển khoản -->
    <div class="col-lg-5">
        <?php if ($invoice['remaining_amount'] > 0): ?>
            <?php
            $bankAcc = get_setting('bank_account_number');
            $bankName = get_setting('bank_name');
            $bankOwner = get_setting('bank_account_name');
            if (!empty($bankAcc)):
                $qrUrl = "https://img.vietqr.io/image/MB-" . urlencode($bankAcc) . "-compact.png?amount=" . (int)$invoice['remaining_amount'] . "&addInfo=" . urlencode($invoice['invoice_code']) . "&accountName=" . urlencode($bankOwner);
            ?>
                <div class="card shadow-sm border-primary text-center mb-4">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-qr-code me-2"></i>Quét Mã Thanh Toán VietQR
                    </div>
                    <div class="card-body p-3">
                        <p class="small text-muted mb-2">Mở App Ngân hàng bất kỳ trên điện thoại và quét mã để thanh toán tự động đúng số tiền:</p>
                        <img src="<?= $qrUrl ?>" class="img-fluid rounded border mb-3 shadow-sm" style="max-height: 240px;" alt="QR Chuyển khoản">

                        <div class="small text-start bg-light p-3 rounded">
                            <div class="mb-1">Ngân hàng: <strong><?= e($bankName) ?></strong></div>
                            <div class="mb-1">Số tài khoản: <span class="font-monospace text-primary fw-bold fs-6"><?= e($bankAcc) ?></span></div>
                            <div class="mb-1">Tên chủ tài khoản: <strong><?= e($bankOwner) ?></strong></div>
                            <div class="mb-1">Số tiền cần trả: <span class="text-danger fw-bold fs-6"><?= format_currency($invoice['remaining_amount']) ?></span></div>
                            <div>Nội dung CK: <span class="badge bg-dark fs-6"><?= e($invoice['invoice_code']) ?></span></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card shadow-sm bg-success bg-opacity-10 border-success text-center p-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold text-success mt-2">Hóa Đơn Đã Được Thanh Toán Đầy Đủ</h5>
                <p class="text-muted small mb-0">Cảm ơn bạn đã thanh toán đúng hạn tiền phòng kỳ tháng <?= e($invoice['billing_month']) ?>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>