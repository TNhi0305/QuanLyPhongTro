<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Tenant;
use App\Models\User;
use Exception;

class TenantController extends Controller
{
    private Tenant $tenantModel;
    private User $userModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->tenantModel = new Tenant();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $tenants = $this->tenantModel->allWithRoomInfo($keyword);

        $this->view('tenants.index', [
            'tenants' => $tenants,
            'keyword' => $keyword
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('tenants.create', [], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $idCard = trim($_POST['id_card'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($fullname) || empty($phone)) {
            set_flash('danger', 'Họ tên và Số điện thoại là bắt buộc.');
            $this->redirect('/tenants/create');
        }

        // Tạo tài khoản đăng nhập cho người thuê nếu được chọn
        $createAccount = !empty($_POST['create_account']);
        $userId = null;

        if ($createAccount) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                set_flash('danger', 'Vui lòng nhập Tên đăng nhập và Mật khẩu cho tài khoản người thuê.');
                $this->redirect('/tenants/create');
            }

            if ($this->userModel->findByUsername($username)) {
                set_flash('danger', "Tên đăng nhập '{$username}' đã tồn tại. Vui lòng chọn tên khác.");
                $this->redirect('/tenants/create');
            }

            $userId = $this->userModel->createUser([
                'username' => $username,
                'password' => $password,
                'role'     => 'tenant',
                'fullname' => $fullname,
                'phone'    => $phone,
                'email'    => $email,
                'status'   => 1
            ]);
        }

        try {
            $tenantId = $this->tenantModel->create([
                'user_id'  => $userId,
                'fullname' => $fullname,
                'phone'    => $phone,
                'email'    => $email,
                'dob'      => $dob,
                'id_card'  => $idCard,
                'address'  => $address,
                'notes'    => $notes
            ]);

            log_activity('Thêm người thuê', 'tenants', $tenantId, "Thêm khách thuê {$fullname} ({$phone})");
            set_flash('success', "Thêm khách thuê {$fullname} thành công!");
            $this->redirect('/tenants');
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi thêm người thuê: ' . $e->getMessage());
            $this->redirect('/tenants/create');
        }
    }

    public function show(int|string $id): void
    {
        $id = (int)$id;
        $tenant = $this->tenantModel->findWithDetails($id);
        if (!$tenant) {
            set_flash('danger', 'Không tìm thấy người thuê.');
            $this->redirect('/tenants');
        }

        $this->view('tenants.show', ['tenant' => $tenant], 'admin');
    }

    public function edit(int|string $id): void
    {
        $id = (int)$id;
        $tenant = $this->tenantModel->findWithDetails($id);
        if (!$tenant) {
            set_flash('danger', 'Không tìm thấy người thuê.');
            $this->redirect('/tenants');
        }

        $this->view('tenants.edit', ['tenant' => $tenant], 'admin');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $tenant = $this->tenantModel->find($id);
        if (!$tenant) {
            set_flash('danger', 'Không tìm thấy người thuê.');
            $this->redirect('/tenants');
        }

        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $idCard = trim($_POST['id_card'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($fullname) || empty($phone)) {
            set_flash('danger', 'Họ tên và Số điện thoại là bắt buộc.');
            $this->redirect('/tenants/edit/' . $id);
        }

        try {
            $this->tenantModel->update($id, [
                'fullname' => $fullname,
                'phone'    => $phone,
                'email'    => $email,
                'dob'      => $dob,
                'id_card'  => $idCard,
                'address'  => $address,
                'notes'    => $notes
            ]);

            // Cập nhật thông tin tài khoản user nếu có liên kết
            if (!empty($tenant['user_id'])) {
                $this->userModel->update($tenant['user_id'], [
                    'fullname' => $fullname,
                    'phone'    => $phone,
                    'email'    => $email
                ]);
            }

            log_activity('Cập nhật người thuê', 'tenants', $id, "Cập nhật thông tin khách thuê {$fullname}");
            set_flash('success', "Cập nhật thông tin khách thuê {$fullname} thành công!");
            $this->redirect('/tenants/show/' . $id);
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi cập nhật: ' . $e->getMessage());
            $this->redirect('/tenants/edit/' . $id);
        }
    }

    public function delete(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        if ($this->tenantModel->hasActiveContracts($id)) {
            set_flash('danger', 'Không thể xóa khách thuê này vì đang có Hợp đồng thuê phòng còn hiệu lực! Vui lòng thanh lý hợp đồng trước.');
            $this->redirect('/tenants');
        }

        $tenant = $this->tenantModel->find($id);
        if ($tenant) {
            $this->tenantModel->delete($id);
            if (!empty($tenant['user_id'])) {
                $this->userModel->delete($tenant['user_id']);
            }
            log_activity('Xóa người thuê', 'tenants', $id, "Đã xóa khách thuê {$tenant['fullname']}");
            set_flash('success', "Đã xóa thông tin khách thuê thành công.");
        }

        $this->redirect('/tenants');
    }
}
