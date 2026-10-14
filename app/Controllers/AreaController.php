<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\RentalArea;

class AreaController extends Controller
{
    private RentalArea $areaModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->areaModel = new RentalArea();
    }

    public function index(): void
    {
        $areas = $this->areaModel->allWithRoomCount();
        $this->view('areas.index', ['areas' => $areas], 'admin');
    }

    public function create(): void
    {
        $this->view('areas.create', [], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $areaCode = strtoupper(trim($_POST['area_code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($areaCode) || empty($name) || empty($address)) {
            set_flash('danger', 'Vui lòng điền đầy đủ Mã khu trọ, Tên khu trọ và Địa chỉ.');
            $this->redirect('/areas/create');
        }

        try {
            $id = $this->areaModel->create([
                'area_code'     => $areaCode,
                'name'          => $name,
                'address'       => $address,
                'contact_phone' => $contactPhone,
                'description'   => $description
            ]);

            log_activity('Thêm khu trọ', 'rental_areas', $id, "Thêm khu trọ {$name} ({$areaCode})");
            set_flash('success', "Thêm khu trọ {$name} thành công!");
            $this->redirect('/areas');
        } catch (\Exception $e) {
            set_flash('danger', 'Lỗi: Mã khu trọ có thể đã tồn tại trong hệ thống.');
            $this->redirect('/areas/create');
        }
    }

    public function edit(int|string $id): void
    {
        $area = $this->areaModel->find((int)$id);
        if (!$area) {
            set_flash('danger', 'Không tìm thấy khu trọ.');
            $this->redirect('/areas');
        }

        $this->view('areas.edit', ['area' => $area], 'admin');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        $area = $this->areaModel->find($id);
        if (!$area) {
            set_flash('danger', 'Không tìm thấy khu trọ.');
            $this->redirect('/areas');
        }

        $areaCode = strtoupper(trim($_POST['area_code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($areaCode) || empty($name) || empty($address)) {
            set_flash('danger', 'Vui lòng điền đầy đủ Mã khu trọ, Tên khu trọ và Địa chỉ.');
            $this->redirect('/areas/edit/' . $id);
        }

        try {
            $this->areaModel->update($id, [
                'area_code'     => $areaCode,
                'name'          => $name,
                'address'       => $address,
                'contact_phone' => $contactPhone,
                'description'   => $description
            ]);

            log_activity('Cập nhật khu trọ', 'rental_areas', $id, "Cập nhật khu trọ {$name}");
            set_flash('success', "Cập nhật khu trọ {$name} thành công!");
            $this->redirect('/areas');
        } catch (\Exception $e) {
            set_flash('danger', 'Lỗi cập nhật khu trọ: ' . $e->getMessage());
            $this->redirect('/areas/edit/' . $id);
        }
    }

    public function delete(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int)$id;

        if ($this->areaModel->hasRooms($id)) {
            set_flash('danger', 'Không thể xóa khu trọ này vì vẫn còn phòng trọ thuộc khu trọ. Vui lòng chuyển hoặc xóa các phòng trước.');
            $this->redirect('/areas');
        }

        $area = $this->areaModel->find($id);
        if ($area) {
            $this->areaModel->delete($id);
            log_activity('Xóa khu trọ', 'rental_areas', $id, "Đã xóa khu trọ {$area['name']}");
            set_flash('success', "Đã xóa khu trọ thành công.");
        }

        $this->redirect('/areas');
    }
}
