<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class RentalArea extends Model
{
    protected string $table = 'rental_areas';

    /**
     * Lấy danh sách khu trọ kèm số lượng phòng và số phòng trống
     */
    public function allWithRoomCount(): array
    {
        $sql = "
            SELECT a.*, 
                   COUNT(r.id) AS total_rooms,
                   SUM(CASE WHEN r.status = 'empty' THEN 1 ELSE 0 END) AS empty_rooms,
                   SUM(CASE WHEN r.status = 'rented' THEN 1 ELSE 0 END) AS rented_rooms,
                   SUM(CASE WHEN r.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_rooms
            FROM rental_areas a
            LEFT JOIN rooms r ON a.id = r.area_id
            GROUP BY a.id
            ORDER BY a.id ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Kiểm tra khu trọ có phòng nào không trước khi xóa
     */
    public function hasRooms(int $areaId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE area_id = ?");
        $stmt->execute([$areaId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
