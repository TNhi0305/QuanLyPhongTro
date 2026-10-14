<?php

namespace App\Models;

use App\Core\Model;
use Exception;
use PDO;

class Contract extends Model
{
    protected string $table = 'contracts';

    /**
     * Lấy danh sách hợp đồng kèm thông tin phòng, người thuê và khu trọ
     */
    public function allWithRelations(array $filters = []): array
    {
        $sql = "
            SELECT c.*, 
                   r.room_code, 
                   r.room_number,
                   a.name AS area_name,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone,
                   t.id_card AS tenant_id_card
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON c.tenant_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['area_id'])) {
            $sql .= " AND r.area_id = ?";
            $params[] = $filters['area_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (c.contract_code LIKE ? OR r.room_code LIKE ? OR t.fullname LIKE ? OR t.phone LIKE ?)";
            $kw = '%' . $filters['keyword'] . '%';
            $params = array_merge($params, [$kw, $kw, $kw, $kw]);
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết hợp đồng đầy đủ thông tin phòng, người thuê và hóa đơn
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "
            SELECT c.*, 
                   r.room_code, 
                   r.room_number,
                   r.area_size,
                   a.name AS area_name,
                   a.address AS area_address,
                   a.contact_phone AS area_phone,
                   t.id AS tenant_id,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone,
                   t.email AS tenant_email,
                   t.dob AS tenant_dob,
                   t.id_card AS tenant_id_card,
                   t.address AS tenant_address
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            JOIN tenants t ON c.tenant_id = t.id
            WHERE c.id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $contract = $stmt->fetch();
        if (!$contract) {
            return null;
        }

        // Lấy danh sách hóa đơn thuộc hợp đồng
        $stmtInv = $this->db->prepare("SELECT * FROM invoices WHERE contract_id = ? ORDER BY billing_month DESC");
        $stmtInv->execute([$id]);
        $contract['invoices'] = $stmtInv->fetchAll();

        return $contract;
    }

    /**
     * Lấy hợp đồng đang hiệu lực của một phòng
     */
    public function getActiveContractByRoom(int $roomId, ?int $excludeContractId = null): ?array
    {
        $sql = "SELECT * FROM contracts WHERE room_id = ? AND status = 'active'";
        $params = [$roomId];
        if ($excludeContractId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeContractId;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Lấy hợp đồng đang hiệu lực của một người thuê
     */
    public function getActiveContractByTenant(int $tenantId): ?array
    {
        $sql = "
            SELECT c.*, r.room_code, r.room_number, a.name AS area_name, a.address AS area_address
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            JOIN rental_areas a ON r.area_id = a.id
            WHERE c.tenant_id = ? AND c.status = 'active'
            ORDER BY c.id DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Tạo mới hợp đồng và tự động cập nhật trạng thái phòng sang 'rented'
     */
    public function createContract(array $data): int
    {
        // Kiểm tra xem phòng đã có hợp đồng hiệu lực nào chưa
        if ($this->getActiveContractByRoom((int)$data['room_id'])) {
            throw new Exception("Phòng này hiện đang có hợp đồng hiệu lực khác. Không thể tạo thêm hợp đồng cùng lúc.");
        }

        $this->db->beginTransaction();
        try {
            $contractId = (int)$this->create($data);

            // Cập nhật trạng thái phòng sang 'rented'
            $stmt = $this->db->prepare("UPDATE rooms SET status = 'rented' WHERE id = ?");
            $stmt->execute([$data['room_id']]);

            $this->db->commit();
            return $contractId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Gia hạn hợp đồng
     */
    public function extendContract(int $id, string $newEndDate, ?string $notes = null): bool
    {
        $contract = $this->find($id);
        if (!$contract) {
            throw new Exception("Hợp đồng không tồn tại.");
        }

        if (strtotime($newEndDate) <= strtotime($contract['end_date'])) {
            throw new Exception("Ngày kết thúc gia hạn phải lớn hơn ngày kết thúc hiện tại (" . format_date($contract['end_date']) . ").");
        }

        $terms = $contract['terms'];
        if (!empty($notes)) {
            $terms .= "\n[Gia hạn ngày " . date('d/m/Y') . "]: Đến ngày " . format_date($newEndDate) . ". " . $notes;
        }

        return $this->update($id, [
            'end_date' => $newEndDate,
            'terms'    => $terms,
            'status'   => 'active'
        ]);
    }

    /**
     * Chấm dứt hợp đồng và cập nhật phòng về trạng thái 'empty'
     */
    public function terminateContract(int $id): bool
    {
        $contract = $this->find($id);
        if (!$contract) {
            throw new Exception("Hợp đồng không tồn tại.");
        }

        $this->db->beginTransaction();
        try {
            $this->update($id, ['status' => 'terminated']);

            // Kiểm tra xem phòng còn hợp đồng active nào khác không
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contracts WHERE room_id = ? AND status = 'active'");
            $stmt->execute([$contract['room_id']]);
            if ((int)$stmt->fetchColumn() === 0) {
                $this->db->prepare("UPDATE rooms SET status = 'empty' WHERE id = ?")->execute([$contract['room_id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
