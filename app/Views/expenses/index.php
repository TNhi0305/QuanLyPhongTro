<?php

/**
 * @var array $expenses
 * @var string $month
 * @var string $category
 * @var float $totalAmount
 */
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Quản Lý Chi Phí Vận Hành</h4>
        <span class="text-muted small">Theo dõi chi phí bảo trì, tiền rác, điện nước chung, sửa chữa khu trọ</span>
    </div>
    <a href="<?= url('/expenses/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm Khoản Chi Mới
    </a>
</div>

<!-- Thẻ tổng chi & Bộ lọc -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100 p-3 bg-danger bg-opacity-10 border-danger border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">TỔNG CHI THEO BỘ LỌC</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1"><?= format_currency($totalAmount) ?></h4>
                    <span class="text-muted small">Tổng số: <?= count($expenses) ?> khoản chi</span>
                </div>
                <div class="stat-icon bg-danger text-white">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100 shadow-sm">
            <div class="card-body p-3">
                <form action="<?= url('/expenses') ?>" method="GET" class="row g-2 align-items-center">
                    <div class="col-sm-5">
                        <label class="form-label small fw-bold text-muted mb-1">Tháng Chi Tiêu</label>
                        <input type="month" class="form-control" name="month" value="<?= e($month) ?>">
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label small fw-bold text-muted mb-1">Hạng Mục Chi</label>
                        <select name="category" class="form-select">
                            <option value="">-- Tất cả hạng mục --</option>
                            <option value="Bảo trì" <?= $category === 'Bảo trì' ? 'selected' : '' ?>>Bảo trì / Sửa chữa</option>
                            <option value="Điện nước chung" <?= $category === 'Điện nước chung' ? 'selected' : '' ?>>Điện nước chung</option>
                            <option value="Vệ sinh" <?= $category === 'Vệ sinh' ? 'selected' : '' ?>>Vệ sinh & Rác</option>
                            <option value="Mua sắm" <?= $category === 'Mua sắm' ? 'selected' : '' ?>>Mua sắm thiết bị</option>
                            <option value="Lương" <?= $category === 'Lương' ? 'selected' : '' ?>>Lương nhân viên / bảo vệ</option>
                            <option value="Khác" <?= $category === 'Khác' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>
                    <div class="col-sm-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bảng chi phí -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã Phiếu Chi</th>
                    <th>Nội Dung Chi Phí</th>
                    <th>Hạng Mục</th>
                    <th>Ngày Chi</th>
                    <th>Người Nhận Tiền</th>
                    <th>Số Tiền (VNĐ)</th>
                    <th class="text-end" style="width: 120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-wallet fs-1 d-block mb-2"></i>
                            Không có khoản chi nào trong kỳ này.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= e($e['expense_code']) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($e['title']) ?></div>
                                <span class="text-muted small"><?= e($e['description'] ?? '') ?></span>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?= e($e['category']) ?></span></td>
                            <td class="small"><?= format_date($e['expense_date']) ?></td>
                            <td><?= !empty($e['recipient']) ? e($e['recipient']) : '-' ?></td>
                            <td><span class="fw-bold text-danger fs-6"><?= format_currency($e['amount']) ?></span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= url('/expenses/edit/' . $e['id']) ?>" class="btn btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= url('/expenses/delete/' . $e['id']) ?>" method="POST" class="d-inline form-delete-confirm" data-confirm="Xóa khoản chi <?= e($e['title']) ?>?">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>