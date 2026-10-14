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
        <span class="text-muted small">Kỳ tính tiền: <strong><?= e($invoice['billing_month']) ?></strong> &bull; Hạn trả: <?= format_date($invoice['due_date']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/invoices/print/' . $invoice['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> In Hóa Đơn
        </a>
        <?php if ($invoice['remaining_amount'] > 0): ?>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-cash-stack"></i> Ghi Nhận Thu Tiền
            </button>
        <?php endif; ?>
        <a href="<?= url('/invoices') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Danh Sách
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Cột thông tin chi tiết hóa đơn -->
    <div class="col-lg-8">
        <!-- Bảng chi tiết từng khoản thu -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-list-check text-primary me-2"></i>Bảng Kê Chi Tiết Các Khoản Thu
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">STT</th>
                            <th>Nội dung mục thu</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-center">ĐVT</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoice['details'])): ?>
                            <tr>
                                <td>1</td>
                                <td>Tiền phòng tháng <?= e($invoice['billing_month']) ?></td>
                                <td class="text-center">1</td>
                                <td class="text-center">Tháng</td>
                                <td class="text-end"><?= format_currency($invoice['room_price']) ?></td>
                                <td class="text-end fw-bold"><?= format_currency($invoice['room_price']) ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoice['details'] as $idx => $d): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($d['item_name']) ?></div>
                                        <?php if (!empty($d['note'])): ?>
                                            <span class="text-muted small"><?= e($d['note']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (float)$d['quantity'] ?></td>
                                    <td class="text-center"><?= e($d['unit']) ?></td>
                                    <td class="text-end"><?= format_currency($d['unit_price']) ?></td>
                                    <td class="text-end fw-bold"><?= format_currency($d['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Tổng cộng hóa đơn:</td>
                            <td class="text-end fw-bold text-dark fs-6"><?= format_currency($invoice['total_amount']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold text-success">Đã thanh toán:</td>
                            <td class="text-end fw-bold text-success">- <?= format_currency($invoice['paid_amount']) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold text-danger fs-6">Còn phải thanh toán:</td>
                            <td class="text-end fw-bold text-danger fs-5"><?= format_currency($invoice['remaining_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php if (!empty($invoice['notes'])): ?>
                <div class="card-footer bg-white small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Ghi chú: <?= e($invoice['notes']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lịch sử các đợt thanh toán -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>Lịch Sử Các Đợt Thanh Toán</span>
                <?php if ($invoice['remaining_amount'] > 0): ?>
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-plus-lg"></i> Thêm phiếu thu
                    </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã Phiếu</th>
                            <th>Ngày Thu</th>
                            <th>Hình Thức</th>
                            <th>Số Tiền</th>
                            <th>Mã Tham Chiếu / Ghi Chú</th>
                            <th>Người Thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoice['payments'])): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">Chưa có giao dịch thanh toán nào được ghi nhận.</td></tr>
                        <?php else: ?>
                            <?php foreach ($invoice['payments'] as $p): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= e($p['payment_code']) ?></span></td>
                                    <td class="small"><?= format_datetime($p['payment_date']) ?></td>
                                    <td>
                                        <?php if ($p['payment_method'] === 'bank_transfer'): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary"><i class="bi bi-bank me-1"></i> Chuyển khoản</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-cash me-1"></i> Tiền mặt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success">+ <?= format_currency($p['amount']) ?></td>
                                    <td class="small text-muted"><?= e($p['transaction_ref'] ?? $p['notes'] ?? '-') ?></td>
                                    <td class="small"><?= e($p['created_by_name'] ?? 'Hệ thống') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cột thông tin phòng, khách và QR thanh toán -->
    <div class="col-lg-4">
        <!-- Thông tin phòng & Người thuê -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-person-badge text-primary me-2"></i>Thông Tin Bên Thuê
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small">Phòng trọ:</span>
                    <div class="fw-bold text-dark fs-5"><?= e($invoice['room_code']) ?> - <?= e($invoice['room_number']) ?></div>
                    <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?= e($invoice['area_name']) ?></span>
                </div>
                <div class="border-top pt-2">
                    <span class="text-muted small">Khách đại diện:</span>
                    <div class="fw-bold text-dark"><?= e($invoice['tenant_name']) ?></div>
                    <div class="small text-muted"><i class="bi bi-telephone"></i> <?= e($invoice['tenant_phone']) ?></div>
                </div>
            </div>
        </div>

        <!-- Thông tin chuyển khoản & VietQR -->
        <?php
        $bankAcc = get_setting('bank_account_number');
        $bankName = get_setting('bank_name');
        $bankOwner = get_setting('bank_account_name');
        if (!empty($bankAcc)):
            // Tạo link VietQR tự động
            $qrUrl = "https://img.vietqr.io/image/MB-" . urlencode($bankAcc) . "-compact.png?amount=" . (int)$invoice['remaining_amount'] . "&addInfo=" . urlencode($invoice['invoice_code']) . "&accountName=" . urlencode($bankOwner);
        ?>
            <div class="card shadow-sm text-center">
                <div class="card-header bg-white fw-bold text-primary">
                    <i class="bi bi-qr-code me-1"></i> Quét Mã Chuyển Khoản VietQR
                </div>
                <div class="card-body p-3">
                    <img src="<?= $qrUrl ?>" class="img-fluid rounded border mb-3 shadow-sm" style="max-height: 220px;" alt="VietQR Thanh Toán">
                    <div class="small text-start bg-light p-2 rounded">
                        <div><strong>Ngân hàng:</strong> <?= e($bankName) ?></div>
                        <div><strong>Số TK:</strong> <span class="font-monospace text-primary fw-bold"><?= e($bankAcc) ?></span></div>
                        <div><strong>Chủ TK:</strong> <?= e($bankOwner) ?></div>
                        <div><strong>Nội dung CK:</strong> <span class="badge bg-dark"><?= e($invoice['invoice_code']) ?></span></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ghi Nhận Thu Tiền -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/payments') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="payModalLabel"><i class="bi bi-cash-coin text-success me-2"></i>Ghi Nhận Thanh Toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        Số tiền còn phải thanh toán: <strong><?= format_currency($invoice['remaining_amount']) ?></strong>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold small">Số Tiền Thu (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" step="1000" class="form-control" id="amount" name="amount" value="<?= (float)$invoice['remaining_amount'] ?>" max="<?= (float)$invoice['remaining_amount'] ?>" min="1000" required>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label fw-bold small">Hình Thức Thanh Toán <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="cash" selected>Tiền mặt</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_ref" class="form-label fw-bold small">Mã Giao Dịch / Tham Chiếu</label>
                        <input type="text" class="form-control" id="transaction_ref" name="transaction_ref" placeholder="Mã giao dịch ngân hàng hoặc số phiếu...">
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label fw-bold small">Thời Gian Thu</label>
                        <input type="datetime-local" class="form-control" id="payment_date" name="payment_date" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold small">Ghi Chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Ghi chú đợt thanh toán..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Lưu Phiếu Thu</button>
                </div>
            </form>
        </div>
    </div>
</div>
