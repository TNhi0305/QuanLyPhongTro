<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không Có Quyền Truy Cập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card shadow-sm border-0 text-center p-4 p-md-5" style="max-width: 500px; border-radius: 1rem;">
        <div class="text-danger mb-3">
            <i class="bi bi-shield-lock-fill" style="font-size: 4rem;"></i>
        </div>
        <h2 class="fw-bold text-dark">403 - Quyền Truy Cập Bị Từ Chối</h2>
        <p class="text-muted mt-2">
            <?= e($message ?? 'Bạn không có quyền truy cập vào chức năng hoặc dữ liệu này.') ?>
        </p>
        <div class="mt-4 d-flex justify-content-center gap-2">
            <a href="<?= url('/') ?>" class="btn btn-primary">
                <i class="bi bi-house-door-fill me-1"></i> Quay về Trang Chủ
            </a>
            <a href="<?= url('/logout') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</body>
</html>
