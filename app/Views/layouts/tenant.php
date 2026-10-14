<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Người Thuê - <?= e(get_setting('app_name', 'Quản Lý Phòng Trọ')) ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Top Navbar cho Người Thuê -->
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary" href="<?= url('/tenant/dashboard') ?>">
            <i class="bi bi-house-door-fill fs-4"></i>
            <span>PHÒNG TRỌ CỦA TÔI</span>
        </a>

        <!-- Desktop Navigation Links -->
        <div class="collapse navbar-collapse d-none d-lg-block" id="tenantNavDesktop">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/dashboard') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= url('/tenant/dashboard') ?>">
                        <i class="bi bi-speedometer2 me-1"></i> Trang Chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/room') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= url('/tenant/room') ?>">
                        <i class="bi bi-door-open me-1"></i> Phòng Đang Thuê
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/contract') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= url('/tenant/contract') ?>">
                        <i class="bi bi-file-text me-1"></i> Hợp Đồng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/invoices') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= url('/tenant/invoices') ?>">
                        <i class="bi bi-receipt me-1"></i> Hóa Đơn & Tiền Điện Nước
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/maintenance') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= url('/tenant/maintenance') ?>">
                        <i class="bi bi-tools me-1"></i> Báo Sửa Chữa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/notifications') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= url('/tenant/notifications') ?>">
                        <i class="bi bi-bell me-1"></i> Thông Báo
                    </a>
                </li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('/tenant/notifications') ?>" class="btn btn-light position-relative p-2 rounded-circle" title="Thông báo">
                <i class="bi bi-bell fs-5 text-secondary"></i>
            </a>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border rounded-pill py-1 px-3" type="button" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="small fw-semibold d-none d-sm-inline"><?= e(auth_user()['fullname'] ?? 'Khách thuê') ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Tài khoản @<?= e(auth_user()['username'] ?? '') ?></h6></li>
                    <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person me-2"></i> Hồ Sơ & Đổi Mật Khẩu</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Content Container -->
<div class="container py-4 tenant-content">
    <!-- Flash Messages -->
    <?php require __DIR__ . '/alerts.php'; ?>

    <!-- Main View Content -->
    <?= $content ?? '' ?>
</div>

<!-- Bottom Navigation Bar trên Điện Thoại (Mobile Navigation) -->
<nav class="bottom-mobile-nav">
    <div class="nav-item">
        <a href="<?= url('/tenant/dashboard') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/dashboard') !== false ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Trang chủ</span>
        </a>
    </div>
    <div class="nav-item">
        <a href="<?= url('/tenant/room') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/room') !== false || strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/contract') !== false ? 'active' : '' ?>">
            <i class="bi bi-door-open-fill"></i>
            <span>Phòng</span>
        </a>
    </div>
    <div class="nav-item">
        <a href="<?= url('/tenant/invoices') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/invoices') !== false || strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/meters') !== false ? 'active' : '' ?>">
            <i class="bi bi-receipt-cutoff"></i>
            <span>Hóa đơn</span>
        </a>
    </div>
    <div class="nav-item">
        <a href="<?= url('/tenant/maintenance') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenant/maintenance') !== false ? 'active' : '' ?>">
            <i class="bi bi-tools"></i>
            <span>Báo hỏng</span>
        </a>
    </div>
    <div class="nav-item">
        <a href="<?= url('/profile') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/profile') !== false ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i>
            <span>Cá nhân</span>
        </a>
    </div>
</nav>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App JS -->
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
