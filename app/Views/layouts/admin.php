<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(get_setting('app_name', 'Quản Lý Phòng Trọ')) ?> - Quản Trị Hệ Thống</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar Backdrop trên Mobile -->
    <div id="sidebarBackdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 1035;"></div>

    <!-- Sidebar Quản Trị -->
    <aside class="admin-sidebar d-flex flex-column flex-shrink-0">
        <a href="<?= url('/dashboard') ?>" class="sidebar-brand">
            <i class="bi bi-building-fill-gear text-primary fs-4"></i>
            <span class="text-truncate">QL PHÒNG TRỌ</span>
        </a>

        <div class="overflow-y-auto flex-grow-1 py-2">
            <a href="<?= url('/dashboard') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') !== false ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Tổng Quan
            </a>

            <a href="<?= url('/users') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/users') !== false ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i> Tài Khoản Người Dùng
            </a>

            <div class="sidebar-heading">QUẢN LÝ PHÒNG & KHÁCH</div>
            <a href="<?= url('/areas') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/areas') !== false ? 'active' : '' ?>">
                <i class="bi bi-geo-alt"></i> Khu Trọ & Dãy Trọ
            </a>
            <a href="<?= url('/rooms') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/rooms') !== false ? 'active' : '' ?>">
                <i class="bi bi-door-open"></i> Phòng Trọ
            </a>
            <a href="<?= url('/tenants') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/tenants') !== false ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Khách Thuê
            </a>
            <a href="<?= url('/contracts') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/contracts') !== false ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Hợp Đồng Thuê
            </a>

            <div class="sidebar-heading">DỊCH VỤ & TÀI CHÍNH</div>
            <a href="<?= url('/meters') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/meters') !== false ? 'active' : '' ?>">
                <i class="bi bi-lightning-charge"></i> Ghi Điện Nước
            </a>
            <a href="<?= url('/invoices') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/invoices') !== false ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Hóa Đơn & Thu Tiền
            </a>
            <a href="<?= url('/payments') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/payments') !== false ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i> Lịch Sử Thu Tiền
            </a>
            <a href="<?= url('/services') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/services') !== false ? 'active' : '' ?>">
                <i class="bi bi-sliders"></i> Giá Điện Nước & Dịch Vụ
            </a>
            <a href="<?= url('/expenses') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/expenses') !== false ? 'active' : '' ?>">
                <i class="bi bi-wallet2"></i> Chi Phí Vận Hành
            </a>

            <div class="sidebar-heading">VẬN HÀNH & BÁO CÁO</div>
            <a href="<?= url('/maintenance') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/maintenance') !== false ? 'active' : '' ?>">
                <i class="bi bi-tools"></i> Sửa Chữa & Bảo Trì
            </a>
            <a href="<?= url('/reports') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/reports') !== false ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i> Báo Cáo Doanh Thu
            </a>
            <a href="<?= url('/notifications') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/notifications') !== false ? 'active' : '' ?>">
                <i class="bi bi-bell"></i> Đăng Thông Báo
            </a>
            <a href="<?= url('/settings') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/settings') !== false ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Cài Đặt Hệ Thống
            </a>
        </div>

        <div class="p-3 border-top border-secondary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between text-white">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="small">
                        <div class="fw-bold text-truncate" style="max-width: 120px;"><?= e(auth_user()['fullname'] ?? 'Admin') ?></div>
                        <div class="text-white-50" style="font-size: 0.75rem;">Quản trị viên</div>
                    </div>
                </div>
                <a href="<?= url('/logout') ?>" class="text-danger p-1" title="Đăng xuất">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-wrapper flex-grow-1 overflow-x-hidden">
        <!-- Top Navbar -->
        <header class="top-navbar px-3 px-lg-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebarToggle" class="btn btn-outline-secondary border-0 p-2 d-lg-none" type="button" aria-label="Menu">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div class="fw-bold text-secondary d-none d-sm-block">
                    <i class="bi bi-house-door-fill text-primary me-1"></i>
                    <?= e(get_setting('app_name', 'Quản Lý Phòng Trọ')) ?>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('/invoices/create') ?>" class="btn btn-primary btn-sm d-none d-md-flex">
                    <i class="bi bi-plus-circle"></i> Lập Hóa Đơn
                </a>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 text-primary"></i>
                        <span class="d-none d-md-inline fw-medium"><?= e(auth_user()['fullname'] ?? 'Admin') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Tài khoản: @<?= e(auth_user()['username'] ?? '') ?></h6></li>
                        <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person me-2"></i> Hồ Sơ Cá Nhân</a></li>
                        <li><a class="dropdown-item" href="<?= url('/settings') ?>"><i class="bi bi-gear me-2"></i> Cài Đặt Hệ Thống</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <main class="content-body">
            <!-- Flash Message Alerts -->
            <?php require __DIR__ . '/alerts.php'; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small mt-auto">
            &copy; <?= date('Y') ?> <?= e(get_setting('app_name', 'Hệ Thống Quản Lý Phòng Trọ')) ?>. Hotline: <?= e(get_setting('hotline', '0901.234.567')) ?>.
        </footer>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App JS -->
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
