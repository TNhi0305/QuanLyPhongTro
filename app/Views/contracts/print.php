<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Hợp Đồng Thuê Phòng - <?= e($contract['contract_code']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Times New Roman', Times, serif;
            font-size: 15pt;
            color: #000;
        }
        .contract-paper {
            background: #fff;
            max-width: 820px;
            margin: 20px auto;
            padding: 45px 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .contract-paper {
                box-shadow: none;
                margin: 0;
                padding: 0;
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
        <i class="bi bi-printer-fill me-1"></i> In Hợp Đồng Này
    </button>
    <button onclick="window.close()" class="btn btn-outline-light">
        <i class="bi bi-x-circle me-1"></i> Đóng Cửa Sổ
    </button>
</div>

<div class="contract-paper">
    <!-- Quốc hiệu & Tiêu ngữ -->
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-1 text-uppercase">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h5>
        <h6 class="fw-bold mb-2">Độc lập - Tự do - Hạnh phúc</h6>
        <div class="mx-auto" style="width: 160px; height: 1.5px; background: #000;"></div>
    </div>

    <!-- Tiêu đề hợp đồng -->
    <div class="text-center my-4">
        <h3 class="fw-bold text-uppercase mb-1">HỢP ĐỒNG THUÊ PHÒNG TRỌ</h3>
        <p class="fst-italic mb-0">Mã hợp đồng: <strong><?= e($contract['contract_code']) ?></strong></p>
        <p class="fst-italic small">Hôm nay, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>, tại <?= e(get_setting('address', 'TP. Hồ Chí Minh')) ?>. Chúng tôi gồm có:</p>
    </div>

    <!-- BÊN A -->
    <div class="mb-3">
        <h6 class="fw-bold text-uppercase mb-2">I. BÊN CHO THUÊ (BÊN A):</h6>
        <table class="w-100 ms-3">
            <tr>
                <td style="width: 160px;">Đại diện:</td>
                <td><strong><?= e(get_setting('app_name', 'Hệ Thống Quản Lý Nhà Trọ An Bình')) ?></strong></td>
            </tr>
            <tr>
                <td>Số điện thoại:</td>
                <td><strong><?= e(get_setting('hotline', '0901.234.567')) ?></strong></td>
            </tr>
            <tr>
                <td>Địa chỉ quản lý:</td>
                <td><?= e(get_setting('address', '123 Huỳnh Tấn Phát, P. Tân Phú, Quận 7, TP.HCM')) ?></td>
            </tr>
        </table>
    </div>

    <!-- BÊN B -->
    <div class="mb-4">
        <h6 class="fw-bold text-uppercase mb-2">II. BÊN THUÊ PHÒNG (BÊN B):</h6>
        <table class="w-100 ms-3">
            <tr>
                <td style="width: 160px;">Họ và tên:</td>
                <td><strong><?= e($contract['tenant_name']) ?></strong></td>
            </tr>
            <tr>
                <td>Số CCCD / CMND:</td>
                <td><strong><?= !empty($contract['tenant_id_card']) ? e($contract['tenant_id_card']) : '..................................................' ?></strong></td>
            </tr>
            <tr>
                <td>Ngày sinh:</td>
                <td><?= format_date($contract['tenant_dob']) ?></td>
            </tr>
            <tr>
                <td>Số điện thoại:</td>
                <td><strong><?= e($contract['tenant_phone']) ?></strong></td>
            </tr>
            <tr>
                <td>Hộ khẩu / Quê quán:</td>
                <td><?= !empty($contract['tenant_address']) ? e($contract['tenant_address']) : '..................................................' ?></td>
            </tr>
        </table>
    </div>

    <p class="mb-3">Sau khi thỏa thuận, hai bên nhất trí ký kết hợp đồng thuê phòng với các điều khoản như sau:</p>

    <!-- ĐIỀU KHOẢN -->
    <div class="mb-3">
        <p class="mb-2"><strong>Điều 1. Đối tượng hợp đồng và thời hạn thuê</strong></p>
        <p class="ms-3 mb-1">- Bên A đồng ý cho Bên B thuê phòng số: <strong><?= e($contract['room_code']) ?> (<?= e($contract['room_number']) ?>)</strong> thuộc khu trọ <strong><?= e($contract['area_name']) ?></strong>.</p>
        <p class="ms-3 mb-1">- Địa chỉ: <?= e($contract['area_address']) ?>. Diện tích: <strong><?= (float)$contract['area_size'] ?> m²</strong>.</p>
        <p class="ms-3 mb-1">- Thời hạn thuê phòng: Từ ngày <strong><?= format_date($contract['start_date']) ?></strong> đến hết ngày <strong><?= format_date($contract['end_date']) ?></strong>.</p>
    </div>

    <div class="mb-3">
        <p class="mb-2"><strong>Điều 2. Giá thuê và phương thức thanh toán</strong></p>
        <p class="ms-3 mb-1">- Giá thuê phòng: <strong><?= format_currency($contract['rent_price']) ?></strong> / tháng (Bằng chữ: ............................................................................).</p>
        <p class="ms-3 mb-1">- Chu kỳ thanh toán: Định kỳ <strong><?= (int)$contract['payment_cycle'] ?> tháng một lần</strong>, thanh toán từ ngày 01 đến ngày 05 đầu mỗi kỳ.</p>
        <p class="ms-3 mb-1">- Tiền điện, tiền nước và các khoản phí dịch vụ (Wifi, rác, gửi xe) sẽ được Bên A tính theo chỉ số tiêu thụ thực tế và bảng giá quy định.</p>
    </div>

    <div class="mb-3">
        <p class="mb-2"><strong>Điều 3. Tiền đặt cọc bảo đảm hợp đồng</strong></p>
        <p class="ms-3 mb-1">- Bên B đặt cọc cho Bên A số tiền là: <strong><?= format_currency($contract['deposit_amount']) ?></strong>.</p>
        <p class="ms-3 mb-1">- Tiền đặt cọc sẽ được Bên A hoàn trả lại cho Bên B sau khi chấm dứt hợp đồng, bàn giao lại phòng và thanh toán đầy đủ các khoản công nợ.</p>
    </div>

    <div class="mb-4">
        <p class="mb-2"><strong>Điều 4. Trách nhiệm và cam kết của hai bên</strong></p>
        <div class="ms-3 mb-2" style="white-space: pre-line;">
            <?= e($contract['terms'] ?? '') ?>
        </div>
        <p class="ms-3 mb-1">- Hợp đồng được lập thành 02 bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản để thực hiện.</p>
    </div>

    <!-- Chữ ký hai bên -->
    <div class="row text-center mt-5 pt-3">
        <div class="col-6">
            <p class="fw-bold text-uppercase mb-1">ĐẠI DIỆN BÊN A</p>
            <p class="fst-italic small mb-5">(Ký và ghi rõ họ tên)</p>
            <div class="mt-5 fw-bold"><?= e(get_setting('app_name', 'Chủ Nhà Trọ')) ?></div>
        </div>
        <div class="col-6">
            <p class="fw-bold text-uppercase mb-1">ĐẠI DIỆN BÊN B</p>
            <p class="fst-italic small mb-5">(Ký và ghi rõ họ tên)</p>
            <div class="mt-5 fw-bold"><?= e($contract['tenant_name']) ?></div>
        </div>
    </div>
</div>

</body>
</html>
