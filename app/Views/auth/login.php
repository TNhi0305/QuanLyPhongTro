<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - <?= e(get_setting('app_name', 'Hệ Thống Quản Lý Phòng Trọ')) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        padding: 1.5rem;
    }

    .login-card {
        background: #ffffff;
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 460px;
        padding: 2.25rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #2563eb, #38bdf8, #10b981);
    }

    .brand-icon-wrapper {
        width: 68px;
        height: 68px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        border-radius: 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.85rem;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }



    .form-control,
    .input-group-text,
    .btn-toggle-pw {
        border-color: #e2e8f0;
        background-color: #f8fafc;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .form-control:focus {
        background-color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .btn-primary-action {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border: none;
        color: #fff;
        padding-top: 0.8rem;
        padding-bottom: 0.8rem;
        border-radius: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
    }

    .btn-primary-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        color: #fff;
    }

    .quick-accounts {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 12px;
        margin-top: 1.25rem;
    }

    .quick-btn {
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
    }

    .quick-btn:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="text-center mb-3">
            <div class="brand-icon-wrapper">
                <i class="bi bi-houses-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Cổng Đăng Nhập</h4>
            <p class="text-muted small mb-0"><?= e(get_setting('app_name', 'Hệ Thống Quản Lý Phòng Trọ')) ?></p>
        </div>

        <!-- Hiển thị thông báo Flash -->
        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center mb-3"
            role="alert">
            <i
                class="bi <?= $flash['type'] === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' ?> me-2 fs-5"></i>
            <div class="small"><?= e($flash['message']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form action="<?= url('/login') ?>" method="POST" id="loginForm">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="username" class="form-label fw-semibold text-secondary small">
                    Tài khoản, Số điện thoại hoặc Email
                </label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 text-muted"><i class="bi bi-person fs-5"></i></span>
                    <input type="text" class="form-control border-start-0 ps-1" id="username" name="username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                        placeholder="Nhập số điện thoại, tài khoản hoặc email..." required autofocus>
                </div>

            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-secondary small">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 text-muted"><i class="bi bi-key fs-5"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0 ps-1" id="password"
                        name="password" placeholder="Nhập mật khẩu..." required>
                    <button class="btn btn-outline-secondary btn-toggle-pw border-start-0" type="button"
                        id="togglePassword" aria-label="Hiện mật khẩu">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-action w-100 shadow-sm mb-2">
                <i class="bi bi-box-arrow-in-right me-2"></i> Đăng Nhập Hệ Thống
            </button>
        </form>

        <!-- Tài khoản mẫu để kiểm thử nhanh
        <div class="quick-accounts" id="quickAccountsWrap">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-secondary">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Đăng nhập nhanh mẫu (1 chạm):
                </span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="quick-btn" onclick="fillLogin('quanli', 'NhaTro@2026')">
                    <i class="bi bi-shield-check text-primary me-1"></i>Quản Trị: <strong>quanli</strong>
                </button>
                <button type="button" class="quick-btn" onclick="fillLogin('0912345678', 'NhaTro@2026')">
                    <i class="bi bi-person text-success me-1"></i>Khách thuê: <strong>An (P.101)</strong>
                </button>
                <button type="button" class="quick-btn" onclick="fillLogin('0987654321', 'NhaTro@2026')">
                    <i class="bi bi-person text-success me-1"></i>Khách thuê: <strong>Bình (P.102)</strong>
                </button>
            </div>
        </div>
    </div> -->

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/bootstrap.bundle.min.js"></script>
        <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                pwInput.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });

        function fillLogin(user, pass) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
        }
        </script>

</body>

</html>