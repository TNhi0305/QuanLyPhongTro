<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Tenant extends Model
{
    protected string $table = 'tenants';

    /**
     * Lấy danh sách người thuê kèm thông tin phòng đang thuê hiện tại
     */
    public function allWithRoomInfo(string $keyword = ''): array
    {
        $sql = "
            SELECT t.*, 
                   u.username,
                   u.status AS account_status,
                   c.id AS contract_id,
                   c.contract_code,
                   c.start_date,
                   c.end_date,
                   c.status AS contract_status,
                   r.id AS room_id,
                   r.room_code,
                   r.room_number,
                   a.name AS area_name
            FROM tenants t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN contracts c ON t.id = c.tenant_id AND c.status = 'active'
            LEFT JOIN rooms r ON c.room_id = r.id
            LEFT JOIN rental_areas a ON r.area_id = a.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (t.fullname LIKE ? OR t.phone LIKE ? OR t.id_card LIKE ? OR r.room_code LIKE ?)";
            $kw = '%' . $keyword . '%';
            $params = [$kw, $kw, $kw, $kw];
        }

        $sql .= " ORDER BY t.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết người thuê kèm lịch sử hợp đồng và hóa đơn
     */
    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username, u.status AS account_status
            FROM tenants t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $tenant = $stmt->fetch();
        if (!$tenant) {
            return null;
        }

        // Lấy lịch sử hợp đồng
        $stmtContract = $this->db->prepare("
            SELECT c.*, r.room_code, r.room_number, a.name AS area_name
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            WHERE c.tenant_id = ?
            ORDER BY c.id DESC
        ");
        $stmtContract->execute([$id]);
        $tenant['contracts'] = $stmtContract->fetchAll();

        return $tenant;
    }

    /**
     * Tìm thông tin người thuê theo user_id
     */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Kiểm tra người thuê có hợp đồng đang có hiệu lực không
     */
    public function hasActiveContracts(int $tenantId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM contracts WHERE tenant_id = ? AND status = 'active'");
        $stmt->execute([$tenantId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
