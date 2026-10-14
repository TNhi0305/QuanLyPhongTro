<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Quản lý tài khoản</h4>
    <p class="text-muted small mb-0">Tạo tài khoản người thuê tại mục Người thuê; tại đây có thể tìm kiếm và khóa/mở khóa tài khoản.</p></div>
</div>
<form method="GET" action="<?= url('/users') ?>" class="card card-body mb-3">
    <div class="row g-2"><div class="col-md-9"><input class="form-control" name="keyword" value="<?= e($keyword) ?>" placeholder="Tìm tên đăng nhập, họ tên, email hoặc số điện thoại"></div>
    <div class="col-md-3 d-grid"><button class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm kiếm</button></div></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th>Tài khoản</th><th>Họ tên</th><th>Liên hệ</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th class="text-end">Thao tác</th></tr></thead><tbody>
<?php if (empty($users)): ?><tr><td colspan="7" class="text-center text-muted py-4">Không tìm thấy tài khoản.</td></tr>
<?php else: foreach ($users as $u): ?><tr>
<td class="fw-semibold"><?= e($u['username']) ?></td><td><?= e($u['fullname']) ?></td>
<td><div><?= e($u['email'] ?? '-') ?></div><small class="text-muted"><?= e($u['phone'] ?? '') ?></small></td>
<td><span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'info' ?>"><?= $u['role'] === 'admin' ? 'Quản trị' : 'Người thuê' ?></span></td>
<td><span class="badge bg-<?= (int)$u['status'] === 1 ? 'success' : 'secondary' ?>"><?= (int)$u['status'] === 1 ? 'Hoạt động' : 'Đã khóa' ?></span></td>
<td><?= e(format_datetime($u['created_at'])) ?></td><td class="text-end">
<?php if ((int)$u['id'] !== (int)(auth_user()['id'] ?? 0)): ?>
<form method="POST" action="<?= url('/users/toggle-status/' . (int)$u['id']) ?>" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn <?= (int)$u['status'] === 1 ? 'khóa' : 'mở khóa' ?> tài khoản này?')">
<?= csrf_field() ?><button class="btn btn-sm btn-<?= (int)$u['status'] === 1 ? 'outline-danger' : 'outline-success' ?>"><i class="bi bi-<?= (int)$u['status'] === 1 ? 'lock' : 'unlock' ?> me-1"></i><?= (int)$u['status'] === 1 ? 'Khóa' : 'Mở khóa' ?></button></form>
<?php else: ?><span class="text-muted small">Tài khoản hiện tại</span><?php endif; ?></td></tr>
<?php endforeach; endif; ?></tbody></table></div></div>
