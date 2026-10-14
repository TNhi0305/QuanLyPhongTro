<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Service;
use Exception;

class ServiceController extends Controller
{
    private Service $serviceModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->serviceModel = new Service();
    }

    public function index(): void
    {
        $services = $this->serviceModel->all('type ASC, id ASC');
        $this->view('services.index', ['services' => $services], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $serviceCode = strtoupper(trim($_POST['service_code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $type = $_POST['type'] ?? 'fixed';
        $description = trim($_POST['description'] ?? '');

        if (empty($serviceCode) || empty($name) || empty($unit)) {
            set_flash('danger', 'Vui lòng nhập đầy đủ Mã dịch vụ, Tên dịch vụ và Đơn vị tính.');
            $this->redirect('/services');
        }

        try {
            $id = $this->serviceModel->create([
                'service_code' => $serviceCode,
                'name'         => $name,
                'unit'         => $unit,
                'price'        => $price,
                'type'         => $type,
                'description'  => $description,
                'is_active'    => 1
            ]);

            log_activity('Thêm dịch vụ', 'services', $id, "Thêm dịch vụ {$name}");
            set_flash('success', "Thêm dịch vụ {$name} thành công!");
        } catch (Exception $e) {
            set_flash('danger', 'Mã dịch vụ có thể đã tồn tại: ' . $e->getMessage());
        }

        $this->redirect('/services');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $service = $this->serviceModel->find($id);
        if (!$service) {
            set_flash('danger', 'Không tìm thấy dịch vụ.');
            $this->redirect('/services');
        }

        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name) || empty($unit)) {
            set_flash('danger', 'Tên dịch vụ và đơn vị tính không được để trống.');
            $this->redirect('/services');
        }

        $this->serviceModel->update($id, [
            'name'        => $name,
            'unit'        => $unit,
            'price'       => $price,
            'description' => $description,
            'is_active'   => $isActive
        ]);

        log_activity('Cập nhật dịch vụ', 'services', $id, "Cập nhật đơn giá dịch vụ {$name}");
        set_flash('success', "Cập nhật đơn giá {$name} thành công!");
        $this->redirect('/services');
    }

    public function delete(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $service = $this->serviceModel->find($id);
        if ($service) {
            $this->serviceModel->delete($id);
            log_activity('Xóa dịch vụ', 'services', $id, "Xóa dịch vụ {$service['name']}");
            set_flash('success', 'Đã xóa dịch vụ.');
        }

        $this->redirect('/services');
    }
}
