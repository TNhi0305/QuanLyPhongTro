<?php

namespace App\Models;

use App\Core\Model;
use Exception;
use PDO;

class MeterReading extends Model
{
    protected string $table = 'meter_readings';

    /**
     * Lấy danh sách chỉ số điện nước theo tháng hoặc khu trọ
     */
    public function allWithRooms(?string $billingMonth = null, ?int $areaId = null): array
    {
        $sql = "
            SELECT m.*, 
                   r.room_code, 
                   r.room_number,
                   a.name AS area_name,
                   c.id AS contract_id,
                   t.fullname AS tenant_name
            FROM meter_readings m
            JOIN rooms r ON m.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            LEFT JOIN contracts c ON r.id = c.room_id AND c.status = 'active'
            LEFT JOIN tenants t ON c.tenant_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($billingMonth)) {
            $sql .= " AND m.billing_month = ?";
            $params[] = $billingMonth;
        }

        if (!empty($areaId)) {
            $sql .= " AND r.area_id = ?";
            $params[] = $areaId;
        }

        $sql .= " ORDER BY m.billing_month DESC, r.room_code ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chỉ số gần nhất trước tháng chỉ định để tự động điền số cũ
     */
    public function getPreviousReading(int $roomId, string $currentMonth): ?array
    {
        $stmt = $this->db->prepare("
            SELECT electricity_new, water_new, billing_month
            FROM meter_readings
            WHERE room_id = ? AND billing_month < ?
            ORDER BY billing_month DESC
            LIMIT 1
        ");
        $stmt->execute([$roomId, $currentMonth]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Tìm bản ghi theo phòng và tháng
     */
    public function findByRoomAndMonth(int $roomId, string $billingMonth): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM meter_readings 
            WHERE room_id = ? AND billing_month = ?
            LIMIT 1
        ");
        $stmt->execute([$roomId, $billingMonth]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lưu chỉ số mới hoặc cập nhật nếu đã tồn tại
     */
    public function saveReading(array $data): int
    {
        // Kiểm tra hợp lệ: Số mới không được nhỏ hơn số cũ
        if ((int)$data['electricity_new'] < (int)$data['electricity_old']) {
            throw new Exception("Chỉ số điện mới (" . $data['electricity_new'] . ") không được nhỏ hơn chỉ số điện cũ (" . $data['electricity_old'] . ").");
        }

        if ((int)$data['water_new'] < (int)$data['water_old']) {
            throw new Exception("Chỉ số nước mới (" . $data['water_new'] . ") không được nhỏ hơn chỉ số nước cũ (" . $data['water_old'] . ").");
        }

        $data['electricity_usage'] = (int)$data['electricity_new'] - (int)$data['electricity_old'];
        $data['water_usage'] = (int)$data['water_new'] - (int)$data['water_old'];

        $existing = $this->findByRoomAndMonth((int)$data['room_id'], $data['billing_month']);
        if ($existing) {
            $this->update($existing['id'], $data);
            return (int)$existing['id'];
        }

        return (int)$this->create($data);
    }
}
