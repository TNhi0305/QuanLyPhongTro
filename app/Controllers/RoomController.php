<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Room;
use App\Models\RentalArea;
use Exception;

class RoomController extends Controller
{
    private Room $roomModel;
    private RentalArea $areaModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->roomModel = new Room();
        $this->areaModel = new RentalArea();
    }

    public function index(): void
    {
        $filters = [
            'area_id' => $_GET['area_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? ''),
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
        ];

        $rooms = $this->roomModel->allWithAreaAndTenant($filters);
        $areas = $this->areaModel->all('name ASC');

        $this->view('rooms.index', [
            'rooms' => $rooms,
            'areas' => $areas,
            'filters' => $filters
        ], 'admin');
    }

    public function create(): void
    {
        $areas = $this->areaModel->all('name ASC');
        if (empty($areas)) {
            set_flash('warning', 'Bạn cần tạo ít nhất một Khu trọ trước khi thêm phòng.');
            $this->redirect('/areas/create');
        }
        $this->view('rooms.create', ['areas' => $areas], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $areaId = (int) ($_POST['area_id'] ?? 0);
        $roomCode = strtoupper(trim($_POST['room_code'] ?? ''));
        $roomNumber = trim($_POST['room_number'] ?? '');
        $areaSize = (float) ($_POST['area_size'] ?? 20);
        $price = (float) ($_POST['price'] ?? 0);
        $deposit = (float) ($_POST['deposit'] ?? 0);
        $maxOccupants = (int) ($_POST['max_occupants'] ?? 2);
        $status = $_POST['status'] ?? 'empty';
        $description = trim($_POST['description'] ?? '');

        if (!$areaId || empty($roomCode) || empty($roomNumber) || $price <= 0) {
            set_flash('danger', 'Vui lòng điền đầy đủ Khu trọ, Mã phòng, Tên phòng và Giá thuê hợp lệ.');
            $this->redirect('/rooms/create');
        }

        try {
            $roomId = $this->roomModel->create([
                'area_id' => $areaId,
                'room_code' => $roomCode,
                'room_number' => $roomNumber,
                'area_size' => $areaSize,
                'price' => $price,
                'deposit' => $deposit,
                'max_occupants' => $maxOccupants,
                'status' => $status,
                'description' => $description
            ]);

            // Xử lý upload nhiều ảnh nếu có
            if (!empty($_FILES['images']['name'][0])) {
                $totalFiles = count($_FILES['images']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        try {
                            $imgPath = upload_image($singleFile, 'rooms');
                            if ($imgPath) {
                                $this->roomModel->addImage($roomId, $imgPath, ($i === 0 ? 1 : 0));
                            }
                        } catch (Exception $e) {
                            // Tiếp tục các file còn lại
                        }
                    }
                }
            }

            log_activity('Thêm phòng', 'rooms', $roomId, "Thêm phòng {$roomCode} - {$roomNumber}");
            set_flash('success', "Thêm phòng {$roomCode} thành công!");
            $this->redirect('/rooms');
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi: Mã phòng có thể đã trùng lặp (' . $e->getMessage() . ')');
            $this->redirect('/rooms/create');
        }
    }

    public function show(int|string $id): void
    {
        $id = (int) $id;
        $room = $this->roomModel->findWithDetails($id);
        if (!$room) {
            set_flash('danger', 'Không tìm thấy phòng trọ.');
            $this->redirect('/rooms');
        }

        $this->view('rooms.show', ['room' => $room], 'admin');
    }

    public function edit(int|string $id): void
    {
        $id = (int) $id;
        $room = $this->roomModel->findWithDetails($id);
        if (!$room) {
            set_flash('danger', 'Không tìm thấy phòng trọ.');
            $this->redirect('/rooms');
        }

        $areas = $this->areaModel->all('name ASC');
        $this->view('rooms.edit', ['room' => $room, 'areas' => $areas], 'admin');
    }

    public function update(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int) $id;

        $room = $this->roomModel->find($id);
        if (!$room) {
            set_flash('danger', 'Không tìm thấy phòng trọ.');
            $this->redirect('/rooms');
        }

        $areaId = (int) ($_POST['area_id'] ?? 0);
        $roomCode = strtoupper(trim($_POST['room_code'] ?? ''));
        $roomNumber = trim($_POST['room_number'] ?? '');
        $areaSize = (float) ($_POST['area_size'] ?? 20);
        $price = (float) ($_POST['price'] ?? 0);
        $deposit = (float) ($_POST['deposit'] ?? 0);
        $maxOccupants = (int) ($_POST['max_occupants'] ?? 2);
        $status = $_POST['status'] ?? $room['status'];
        $description = trim($_POST['description'] ?? '');

        if (!$areaId || empty($roomCode) || empty($roomNumber) || $price <= 0) {
            set_flash('danger', 'Vui lòng điền đầy đủ Khu trọ, Mã phòng, Tên phòng và Giá thuê hợp lệ.');
            $this->redirect('/rooms/edit/' . $id);
        }

        try {
            $this->roomModel->update($id, [
                'area_id' => $areaId,
                'room_code' => $roomCode,
                'room_number' => $roomNumber,
                'area_size' => $areaSize,
                'price' => $price,
                'deposit' => $deposit,
                'max_occupants' => $maxOccupants,
                'status' => $status,
                'description' => $description
            ]);

            // Xử lý upload thêm ảnh mới
            if (!empty($_FILES['images']['name'][0])) {
                $totalFiles = count($_FILES['images']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        try {
                            $imgPath = upload_image($singleFile, 'rooms');
                            if ($imgPath) {
                                $this->roomModel->addImage($id, $imgPath, 0);
                            }
                        } catch (Exception $e) {
                            // Bỏ qua lỗi 1 file
                        }
                    }
                }
            }

            $rawImageUrls = trim((string) ($_POST['image_urls'] ?? ''));
            if ($rawImageUrls !== '') {
                $imageUrls = preg_split('/\r\n|\r|\n/', $rawImageUrls);
                $savedUrls = [];

                foreach ($imageUrls as $imageUrl) {
                    $imageUrl = trim($imageUrl);
                    if ($imageUrl === '' || in_array($imageUrl, $savedUrls, true)) {
                        continue;
                    }

                    $validated = filter_var($imageUrl, FILTER_VALIDATE_URL);
                    $scheme = strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME));
                    $host = parse_url($imageUrl, PHP_URL_HOST);

                    if (!$validated || !in_array($scheme, ['http', 'https'], true) || !$host) {
                        throw new \InvalidArgumentException('URL ảnh không hợp lệ: ' . $imageUrl);
                    }

                    $this->roomModel->addImage($id, $imageUrl, 0);
                    $savedUrls[] = $imageUrl;
                }
            }

            log_activity('Cập nhật phòng', 'rooms', $id, "Cập nhật thông tin phòng {$roomCode}");
            set_flash('success', "Cập nhật phòng {$roomCode} thành công!");
            $this->redirect('/rooms/show/' . $id);
        } catch (Exception $e) {
            set_flash('danger', 'Lỗi cập nhật: ' . $e->getMessage());
            $this->redirect('/rooms/edit/' . $id);
        }
    }

    public function delete(int|string $id): void
    {
        $this->validateCsrf();
        $id = (int) $id;

        // KIỂM TRA RÀNG BUỘC: Không cho xóa phòng đang có hợp đồng còn hiệu lực
        if ($this->roomModel->hasActiveContract($id)) {
            set_flash('danger', 'Không thể xóa phòng này vì đang có Hợp đồng thuê phòng còn hiệu lực! Vui lòng thanh lý hợp đồng trước khi xóa phòng.');
            $this->redirect('/rooms');
        }

        $room = $this->roomModel->find($id);
        if ($room) {
            // Xóa từng ảnh an toàn: ảnh upload sẽ xóa file nội bộ,
            // URL bên ngoài chỉ xóa bản ghi trong database.
            $images = $this->roomModel->getImages($id);
            foreach ($images as $img) {
                $this->roomModel->deleteImage((int) $img['id']);
            }

            $this->roomModel->delete($id);
            log_activity('Xóa phòng', 'rooms', $id, "Đã xóa phòng {$room['room_code']}");
            set_flash('success', "Đã xóa phòng {$room['room_code']} thành công.");
        }

        $this->redirect('/rooms');
    }

    public function deleteImage(int|string $id): void
    {
        $this->validateCsrf();
        $imageId = (int) $id;
        $roomId = (int) ($_POST['room_id'] ?? 0);

        $this->roomModel->deleteImage($imageId);
        set_flash('success', 'Đã xóa hình ảnh phòng.');
        $this->redirect('/rooms/edit/' . $roomId);
    }

    public function setPrimaryImage(int|string $id): void
    {
        $this->validateCsrf();
        $imageId = (int) $id;
        $roomId = (int) ($_POST['room_id'] ?? 0);

        $this->roomModel->setPrimaryImage($roomId, $imageId);
        set_flash('success', 'Đã chọn làm hình ảnh đại diện chính.');
        $this->redirect('/rooms/edit/' . $roomId);
    }
}