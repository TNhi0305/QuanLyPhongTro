<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\MeterReading;
use App\Models\Room;
use App\Models\RentalArea;
use Exception;

class MeterController extends Controller
{
    private MeterReading $meterModel;
    private Room $roomModel;
    private RentalArea $areaModel;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->meterModel = new MeterReading();
        $this->roomModel = new Room();
        $this->areaModel = new RentalArea();
    }

    public function index(): void
    {
        $billingMonth = $_GET['billing_month'] ?? date('Y-m');
        $areaId = !empty($_GET['area_id']) ? (int)$_GET['area_id'] : null;

        $readings = $this->meterModel->allWithRooms($billingMonth, $areaId);
        $areas = $this->areaModel->all('name ASC');

        $this->view('meters.index', [
            'readings'     => $readings,
            'areas'        => $areas,
            'billingMonth' => $billingMonth,
            'areaId'       => $areaId
        ], 'admin');
    }

    public function create(): void
    {
        // Lấy tất cả phòng đang thuê hoặc đang quản lý
        $rooms = $this->roomModel->allWithAreaAndTenant();
        $defaultMonth = date('Y-m');

        $this->view('meters.create', [
            'rooms'        => $rooms,
            'defaultMonth' => $defaultMonth
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $roomId = (int)($_POST['room_id'] ?? 0);
        $billingMonth = trim($_POST['billing_month'] ?? date('Y-m'));
        $elecOld = (int)($_POST['electricity_old'] ?? 0);
        $elecNew = (int)($_POST['electricity_new'] ?? 0);
        $waterOld = (int)($_POST['water_old'] ?? 0);
        $waterNew = (int)($_POST['water_new'] ?? 0);
        $recordedDate = $_POST['recorded_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');

        if (!$roomId || empty($billingMonth)) {
            set_flash('danger', 'Vui lòng chọn phòng và kỳ tính tiền.');
            $this->redirect('/meters/create');
        }

        try {
            $id = $this->meterModel->saveReading([
                'room_id'         => $roomId,
                'billing_month'   => $billingMonth,
                'electricity_old' => $elecOld,
                'electricity_new' => $elecNew,
                'water_old'       => $waterOld,
                'water_new'       => $waterNew,
                'recorded_date'   => $recordedDate,
                'notes'           => $notes
            ]);

            log_activity('Ghi điện nước', 'meter_readings', $id, "Ghi chỉ số điện nước phòng ID {$roomId} kỳ {$billingMonth}");
            set_flash('success', "Ghi nhận chỉ số điện nước kỳ {$billingMonth} thành công!");
            $this->redirect('/meters?billing_month=' . $billingMonth);
        } catch (Exception $e) {
            set_flash('danger', $e->getMessage());
            $this->redirect('/meters/create');
        }
    }

    /**
     * API AJAX lấy số cũ của phòng để tự động điền form
     */
    public function getPrevReadingAjax(): void
    {
        $roomId = (int)($_GET['room_id'] ?? 0);
        $month = trim($_GET['billing_month'] ?? date('Y-m'));

        if (!$roomId) {
            $this->json(['success' => false, 'message' => 'Thiếu room_id']);
        }

        $prev = $this->meterModel->getPreviousReading($roomId, $month);
        $current = $this->meterModel->findByRoomAndMonth($roomId, $month);

        $this->json([
            'success'         => true,
            'electricity_old' => $current ? (int)$current['electricity_old'] : ($prev ? (int)$prev['electricity_new'] : 0),
            'water_old'       => $current ? (int)$current['water_old'] : ($prev ? (int)$prev['water_new'] : 0),
            'electricity_new' => $current ? (int)$current['electricity_new'] : '',
            'water_new'       => $current ? (int)$current['water_new'] : '',
            'is_existing'     => (bool)$current
        ]);
    }
}
