<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-person-plus-fill text-primary me-2"></i>Thêm Khách Thuê Mới</h4>
                <span class="text-muted small">Nhập thông tin người thuê phòng và tùy chọn cấp tài khoản ứng dụng</span>
            </div>
            <a href="<?= url('/tenants') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay Lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="<?= url('/tenants') ?>" method="POST">
                    <?= csrf_field() ?>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Thông Tin Cá Nhân</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="fullname" class="form-label fw-bold small">Họ Và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Ví dụ: Nguyễn Văn A" required autofocus>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold small">Số Điện Thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Ví dụ: 0912345678" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold small">Địa Chỉ Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nguyenvana@gmail.com">
                        </div>

                        <div class="col-md-6">
                            <label for="dob" class="form-label fw-bold small">Ngày Sinh</label>
                            <input type="date" class="form-control" id="dob" name="dob">
                        </div>

                        <div class="col-md-6">
                            <label for="id_card" class="form-label fw-bold small">Số Giấy Tờ Tùy Thân (CCCD / CMND)</label>
                            <input type="text" class="form-control" id="id_card" name="id_card" placeholder="Số căn cước công dân...">
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label fw-bold small">Địa Chỉ Thường Trú / Quê Quán</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Tỉnh / Huyện / Xã...">
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-bold small">Ghi Chú Về Khách Thuê</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Ghi chú về nghề nghiệp, trường học, số xe máy, người ở cùng..."></textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Tài Khoản Đăng Nhập Điện Thoại (Tenant Portal)</h6>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="create_account" name="create_account" value="1" checked onchange="toggleAccountFields(this)">
                        <label class="form-check-label fw-bold small" for="create_account">Cấp tài khoản đăng nhập portal cho khách thuê này</label>
                    </div>

                    <div id="accountFields" class="row g-3 bg-light p-3 rounded mb-4">
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-bold small">Tên Đăng Nhập</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Ví dụ: nguyenvana">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold small">Mật Khẩu Ban Đầu</label>
                            <input type="text" class="form-control" id="password" name="password" value="tenant123">
                            <div class="form-text">Mật khẩu mặc định khuyến nghị: tenant123</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="<?= url('/tenants') ?>" class="btn btn-light">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Lưu Người Thuê
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAccountFields(checkbox) {
    const fields = document.getElementById('accountFields');
    if (checkbox.checked) {
        fields.classList.remove('d-none');
    } else {
        fields.classList.add('d-none');
    }
}
</script>
