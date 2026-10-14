<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Hóa Đơn - <?= e($invoice['invoice_code']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
        }
        .invoice-box {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            line-height: 1.5;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .invoice-box {
                box-shadow: none;
                margin: 0;
                padding: 10px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white d-flex justify-content-center gap-3">
    <button onclick="window.print()" class="btn btn-primary fw-bold">
        <i class="bi bi-printer-fill me-1"></i> In Hóa Đơn Này
    </button>
    <button onclick="window.close()" class="btn btn-outline-light">
        <i class="bi bi-x-circle me-1"></i> Đóng Cửa Sổ
    </button>
</div>

<div class="invoice-box">
    <!-- Header Thông tin chủ trọ -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
        <div class="col-7">
            <h5 class="fw-bold text-uppercase mb-1"><?= e(get_setting('app_name', 'HỆ THỐNG QUẢN LÝ PHÒNG TRỌ AN BÌNH')) ?></h5>
            <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e(get_setting('address', '123 Huỳnh Tấn Phát, P. Tân Phú, Quận 7, TP.HCM')) ?></div>
            <div class="small text-muted"><i class="bi bi-telephone"></i> Hotline: <?= e(get_setting('hotline', '0901.234.567')) ?></div>
        </div>
        <div class="col-5 text-end">
            <h4 class="fw-bold text-primary mb-1">PHIẾU THU TIỀN TRỌ</h4>
            <div class="fw-bold">Mã số: <?= e($invoice['invoice_code']) ?></div>
            <div class="small text-muted">Kỳ: Tháng <?= e($invoice['billing_month']) ?></div>
            <div class="small text-muted">Ngày xuất: <?= date('d/m/Y') ?></div>
        </div>
    </div>

    <!-- Thông tin khách thuê & Phòng -->
    <div class="row mb-4 bg-light p-3 rounded mx-0">
        <div class="col-6">
            <div>Khách thuê: <strong><?= e($invoice['tenant_name']) ?></strong></div>
            <div>Số điện thoại: <strong><?= e($invoice['tenant_phone']) ?></strong></div>
        </div>
        <div class="col-6">
            <div>Phòng: <strong><?= e($invoice['room_code']) ?> (<?= e($invoice['room_number']) ?>)</strong></div>
            <div>Khu trọ: <strong><?= e($invoice['area_name']) ?></strong></div>
            <div>Hạn thanh toán: <strong><?= format_date($invoice['due_date']) ?></strong></div>
        </div>
    </div>

    <!-- Bảng chi tiết dịch vụ -->
    <table class="table table-bordered mb-4">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 40px;">STT</th>
                <th>Khoản thu</th>
                <th style="width: 80px;">Số lượng</th>
                <th style="width: 70px;">ĐVT</th>
                <th style="width: 120px;" class="text-end">Đơn giá</th>
                <th style="width: 130px;" class="text-end">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($invoice['details'])): ?>
                <tr>
                    <td class="text-center">1</td>
                    <td>Tiền thuê phòng kỳ <?= e($invoice['billing_month']) ?></td>
                    <td class="text-center">1</td>
                    <td class="text-center">Tháng</td>
                    <td class="text-end"><?= format_currency($invoice['room_price']) ?></td>
                    <td class="text-end fw-bold"><?= format_currency($invoice['room_price']) ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($invoice['details'] as $idx => $d): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td>
                            <div class="fw-bold"><?= e($d['item_name']) ?></div>
                            <?php if (!empty($d['note'])): ?>
                                <small class="text-muted"><?= e($d['note']) ?></small>
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
        <tfoot>
            <tr>
                <td colspan="5" class="text-end fw-bold">Tổng tiền hóa đơn:</td>
                <td class="text-end fw-bold fs-6"><?= format_currency($invoice['total_amount']) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end text-success fw-bold">Số tiền đã thanh toán:</td>
                <td class="text-end text-success fw-bold"><?= format_currency($invoice['paid_amount']) ?></td>
            </tr>
            <tr class="table-light">
                <td colspan="5" class="text-end text-danger fw-bold fs-6">SỐ TIỀN CÒN PHẢI TRẢ:</td>
                <td class="text-end text-danger fw-bold fs-6"><?= format_currency($invoice['remaining_amount']) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Hướng dẫn thanh toán chuyển khoản -->
    <?php
    $bankAcc = get_setting('bank_account_number');
    $bankName = get_setting('bank_name');
    $bankOwner = get_setting('bank_account_name');
    if (!empty($bankAcc)):
    ?>
        <div class="p-3 border rounded mb-4 bg-light small">
            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-bank me-1"></i> Thông Tin Chuyển Khoản Ngân Hàng</h6>
            <div>Ngân hàng: <strong><?= e($bankName) ?></strong> &bull; Số tài khoản: <strong><?= e($bankAcc) ?></strong></div>
            <div>Chủ tài khoản: <strong><?= e($bankOwner) ?></strong></div>
            <div>Nội dung chuyển khoản: <span class="badge bg-dark"><?= e($invoice['invoice_code']) ?> <?= e($invoice['room_code']) ?></span></div>
        </div>
    <?php endif; ?>

    <!-- Chữ ký người nộp và người thu -->
    <div class="row text-center mt-4 pt-2">
        <div class="col-6">
            <p class="fw-bold mb-1">NGƯỜI THU TIỀN</p>
            <p class="fst-italic small mb-5">(Ký, ghi rõ họ tên)</p>
            <div class="mt-5 fw-bold"><?= e(get_setting('app_name', 'Ban Quản Lý')) ?></div>
        </div>
        <div class="col-6">
            <p class="fw-bold mb-1">NGƯỜI NỘP TIỀN</p>
            <p class="fst-italic small mb-5">(Ký, ghi rõ họ tên)</p>
            <div class="mt-5 fw-bold"><?= e($invoice['tenant_name']) ?></div>
        </div>
    </div>
</div>

</body>
</html>
