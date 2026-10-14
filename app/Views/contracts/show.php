<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">Hợp Đồng: <?= e($contract['contract_code']) ?></h4>
            <?php if ($contract['status'] === 'active'): ?>
                <span class="badge bg-success">Đang hiệu lực</span>
            <?php elseif ($contract['status'] === 'expired'): ?>
                <span class="badge bg-warning text-dark">Đã hết hạn</span>
            <?php else: ?>
                <span class="badge bg-secondary">Đã chấm dứt</span>
            <?php endif; ?>
        </div>
        <span class="text-muted small">Thời hạn: <?= format_date($contract['start_date']) ?> đến <?= format_date($contract['end_date']) ?></span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('/contracts/print/' . $contract['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> In Hợp Đồng
        </a>
        <?php if ($contract['status'] === 'active'): ?>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#extendContractModal">
                <i class="bi bi-calendar-plus"></i> Gia Hạn
            </button>
            <form action="<?= url('/contracts/terminate/' . $contract['id']) ?>" method="POST" class="d-inline form-delete-confirm" data-confirm="Bạn có chắc chắn muốn thanh lý và chấm dứt hợp đồng này? Phòng sẽ được chuyển về trạng thái TRỐNG.">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-octagon"></i> Thanh Lý HĐ
                </button>
            </form>
        <?php endif; ?>
        <a href="<?= url('/contracts') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Danh Sách
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Thông tin phòng & Người thuê -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-door-open text-primary me-2"></i>Phòng Đang Thuê: <?= e($contract['room_code']) ?>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Khu trọ:</span>
                        <span class="fw-bold"><?= e($contract['area_name']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Địa chỉ:</span>
                        <span><?= e($contract['area_address']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Diện tích phòng:</span>
                        <span><?= (float)$contract['area_size'] ?> m²</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Giá thuê ký kết:</span>
                        <span class="fw-bold text-primary fs-6"><?= format_currency($contract['rent_price']) ?> / tháng</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Tiền đặt cọc:</span>
                        <span class="fw-bold text-success fs-6"><?= format_currency($contract['deposit_amount']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Chu kỳ thanh toán:</span>
                        <span><?= (int)$contract['payment_cycle'] ?> tháng / lần</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Người đại diện thuê -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-person text-primary me-2"></i>Người Đứng Tên Hợp Đồng
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Họ và tên:</span>
                        <span class="fw-bold text-dark fs-6"><?= e($contract['tenant_name']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Số điện thoại:</span>
                        <span class="fw-bold text-primary"><?= e($contract['tenant_phone']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">CCCD / CMND:</span>
                        <span class="font-monospace fw-semibold"><?= !empty($contract['tenant_id_card']) ? e($contract['tenant_id_card']) : '-' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Email:</span>
                        <span><?= !empty($contract['tenant_email']) ? e($contract['tenant_email']) : '-' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Ngày sinh:</span>
                        <span><?= format_date($contract['tenant_dob']) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Địa chỉ thường trú:</span>
                        <span><?= !empty($contract['tenant_address']) ? e($contract['tenant_address']) : '-' ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Điều khoản cam kết -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-card-checklist text-primary me-2"></i>Điều Khoản Hợp Đồng & Cam Kết
            </div>
            <div class="card-body">
                <p class="text-secondary small mb-0" style="white-space: pre-line; line-height: 1.7;">
                    <?= e($contract['terms'] ?? 'Chưa ghi nhận điều khoản riêng biệt.') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Hóa đơn thuộc hợp đồng này -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-receipt text-primary me-2"></i>Danh Sách Hóa Đơn Thuộc Hợp Đồng Này</span>
                <a href="<?= url('/invoices/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Lập Hóa Đơn Mới
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã Hóa Đơn</th>
                            <th>Kỳ Tính Tiền</th>
                            <th>Tổng Tiền</th>
                            <th>Đã Thu</th>
                            <th>Còn Nợ</th>
                            <th>Trạng Thái</th>
                            <th class="text-end">Xem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contract['invoices'])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">Chưa có hóa đơn nào được phát hành cho hợp đồng này.</td></tr>
                        <?php else: ?>
                            <?php foreach ($contract['invoices'] as $inv): ?>
                                <tr>
                                    <td><span class="fw-bold"><?= e($inv['invoice_code']) ?></span></td>
                                    <td><?= e($inv['billing_month']) ?></td>
                                    <td class="fw-bold"><?= format_currency($inv['total_amount']) ?></td>
                                    <td class="text-success"><?= format_currency($inv['paid_amount']) ?></td>
                                    <td class="text-danger fw-bold"><?= format_currency($inv['remaining_amount']) ?></td>
                                    <td>
                                        <?php if ($inv['status'] === 'paid'): ?>
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                            <span class="badge bg-info text-dark">Thanh toán 1 phần</span>
                                        <?php elseif ($inv['status'] === 'overdue'): ?>
                                            <span class="badge bg-danger">Quá hạn</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('/invoices/show/' . $inv['id']) ?>" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gia Hạn Hợp Đồng -->
<div class="modal fade" id="extendContractModal" tabindex="-1" aria-labelledby="extendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/contracts/extend/' . $contract['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="extendModalLabel"><i class="bi bi-calendar-plus text-primary me-2"></i>Gia Hạn Hợp Đồng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Ngày kết thúc hiện tại: <strong><?= format_date($contract['end_date']) ?></strong></p>
                    <div class="mb-3">
                        <label for="new_end_date" class="form-label fw-bold small">Ngày Kết Thúc Gia Hạn Mới <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="new_end_date" name="new_end_date" value="<?= date('Y-m-d', strtotime($contract['end_date'] . ' +1 year')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold small">Ghi Chú Gia Hạn</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Ví dụ: Hai bên thống nhất gia hạn thêm 12 tháng với giá phòng không đổi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Xác Nhận Gia Hạn</button>
                </div>
            </form>
        </div>
    </div>
</div>
