<?php

namespace App\Models;

use App\Core\Model;
use Exception;
use PDO;

class MaintenanceRequest extends Model
{
    protected string $table = 'maintenance_requests';

    /**
     * Lấy danh sách yêu cầu bảo trì kèm thông tin phòng và người gửi
     */
    public function allWithRelations(array $filters = []): array
    {
        $sql = "
            SELECT m.*, 
                   r.room_code, 
                   r.room_number,
                   a.name AS area_name,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone
            FROM maintenance_requests m
            JOIN rooms r ON m.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON m.tenant_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND m.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND m.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['room_id'])) {
            $sql .= " AND m.room_id = ?";
            $params[] = $filters['room_id'];
        }

        if (!empty($filters['tenant_id'])) {
            $sql .= " AND m.tenant_id = ?";
            $params[] = $filters['tenant_id'];
        }

        $sql .= " ORDER BY CASE m.status WHEN 'pending' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END, m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết yêu cầu bảo trì
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "
            SELECT m.*, 
                   r.room_code, 
                   r.room_number,
                   a.name AS area_name,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone,
                   t.email AS tenant_email
            FROM maintenance_requests m
            JOIN rooms r ON m.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON m.tenant_id = t.id
            WHERE m.id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Tạo yêu cầu mới với mã duy nhất
     */
    public function createRequest(array $data): int
    {
        if (empty($data['request_code'])) {
            $prefix = 'SC-' . date('Ym') . '-';
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM maintenance_requests WHERE DATE_FORMAT(created_at, '%Y%m') = ?");
            $stmtCount->execute([date('Ym')]);
            $seq = (int)$stmtCount->fetchColumn() + 1;
            $data['request_code'] = $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
        }
        return (int)$this->create($data);
    }
}
