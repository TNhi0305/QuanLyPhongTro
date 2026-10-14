<div class="row g-4">
    <div class="col-12">
        <h4 class="fw-bold mb-1"><i class="bi bi-person-circle text-primary me-2"></i>Hồ Sơ Cá Nhân</h4>
        <p class="text-muted small">Cập nhật thông tin tài khoản và đổi mật khẩu bảo mật</p>
    </div>

    <!-- Cập nhật thông tin -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Thông Tin Tài Khoản
            </div>
            <div class="card-body">
                <form action="<?= url('/profile') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên đăng nhập</label>
                        <input type="text" class="form-control bg-light" value="<?= e($user['username'] ?? '') ?>" disabled>
                        <div class="form-text">Tên đăng nhập không thể thay đổi.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Vai trò hệ thống</label>
                        <div>
                            <span class="badge bg-<?= ($user['role'] ?? '') === 'admin' ? 'primary' : 'success' ?> fs-6">
                                <?= ($user['role'] ?? '') === 'admin' ? 'Quản Trị Viên' : 'Người Thuê Phòng' ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fullname" class="form-label fw-bold small">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?= e($user['fullname'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold small">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small">Địa chỉ Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($user['email'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Lưu Thay Đổi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Đổi mật khẩu -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <i class="bi bi-shield-lock text-primary me-2"></i>Đổi Mật Khẩu
            </div>
            <div class="card-body">
                <form action="<?= url('/change-password') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold small">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Nhập mật khẩu đang dùng...">
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold small">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Tối thiểu 6 ký tự...">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-bold small">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Nhập lại mật khẩu mới...">
                    </div>

                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-key-fill me-1"></i> Đổi Mật Khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
