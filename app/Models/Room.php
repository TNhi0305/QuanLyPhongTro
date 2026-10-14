<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use InvalidArgumentException;

class Room extends Model
{
    protected string $table = 'rooms';

    /**
     * Kiểm tra đường dẫn ảnh có phải URL HTTP/HTTPS hay không.
     */
    private function isExternalImageUrl(string $path): bool
    {
        $scheme = strtolower((string) parse_url($path, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * Kiểm tra URL ảnh hợp lệ.
     */
    private function validateExternalImageUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true)
            && !empty(parse_url($url, PHP_URL_HOST));
    }

    /**
     * Lấy danh sách phòng kèm thông tin khu trọ và người thuê hiện tại.
     */
    public function allWithAreaAndTenant(array $filters = []): array
    {
        $sql = "
            SELECT r.*,
                   a.name AS area_name,
                   a.area_code,
                   c.id AS current_contract_id,
                   c.contract_code,
                   t.fullname AS current_tenant_name,
                   t.phone AS current_tenant_phone,
                   (
                       SELECT image_path
                       FROM room_images
                       WHERE room_id = r.id
                       ORDER BY is_primary DESC, id ASC
                       LIMIT 1
                   ) AS primary_image
            FROM rooms r
            JOIN rental_areas a ON r.area_id = a.id
            LEFT JOIN contracts c
                ON r.id = c.room_id AND c.status = 'active'
            LEFT JOIN tenants t ON c.tenant_id = t.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['area_id'])) {
            $sql .= " AND r.area_id = ?";
            $params[] = $filters['area_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                r.room_code LIKE ?
                OR r.room_number LIKE ?
                OR a.name LIKE ?
            )";

            $kw = '%' . $filters['keyword'] . '%';

            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND r.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND r.price <= ?";
            $params[] = $filters['max_price'];
        }

        $sql .= " ORDER BY a.id ASC, r.room_code ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết phòng kèm thông tin khu trọ,
     * hợp đồng đang hoạt động và danh sách ảnh.
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "
            SELECT r.*,
                   a.name AS area_name,
                   a.address AS area_address,
                   a.contact_phone AS area_contact,
                   c.id AS current_contract_id,
                   c.contract_code,
                   c.start_date AS contract_start,
                   c.end_date AS contract_end,
                   c.rent_price AS agreed_rent,
                   t.id AS tenant_id,
                   t.fullname AS tenant_name,
                   t.phone AS tenant_phone,
                   t.email AS tenant_email
            FROM rooms r
            JOIN rental_areas a ON r.area_id = a.id
            LEFT JOIN contracts c
                ON r.id = c.room_id AND c.status = 'active'
            LEFT JOIN tenants t ON c.tenant_id = t.id
            WHERE r.id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            return null;
        }

        $room['images'] = $this->getImages($id);

        return $room;
    }

    /**
     * Lấy danh sách ảnh phòng.
     * image_path có thể là URL bên ngoài hoặc đường dẫn ảnh nội bộ.
     */
    public function getImages(int $roomId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM room_images
            WHERE room_id = ?
            ORDER BY is_primary DESC, id ASC
        ");

        $stmt->execute([$roomId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm ảnh cho phòng.
     *
     * Ví dụ URL:
     * https://example.com/phong-tro.jpg
     *
     * Ví dụ đường dẫn nội bộ:
     * abc123.jpg
     */
    public function addImage(
        int $roomId,
        string $imagePath,
        int $isPrimary = 0
    ): int {
        $imagePath = trim($imagePath);

        if ($imagePath === '') {
            throw new InvalidArgumentException(
                'Đường dẫn hình ảnh không được để trống.'
            );
        }

        // Nếu là URL bên ngoài, chỉ chấp nhận HTTP/HTTPS hợp lệ.
        if (
            preg_match('/^[a-z][a-z0-9+.-]*:/i', $imagePath)
            && !$this->validateExternalImageUrl($imagePath)
        ) {
            throw new InvalidArgumentException(
                'URL hình ảnh không hợp lệ. Chỉ chấp nhận HTTP/HTTPS.'
            );
        }

        $isPrimary = $isPrimary ? 1 : 0;

        if ($isPrimary) {
            $this->db->prepare("
                UPDATE room_images
                SET is_primary = 0
                WHERE room_id = ?
            ")->execute([$roomId]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO room_images
                (room_id, image_path, is_primary, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([
            $roomId,
            $imagePath,
            $isPrimary
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Xóa ảnh của phòng.
     *
     * Chỉ xóa file vật lý nằm trong public/uploads.
     * URL ảnh bên ngoài chỉ bị xóa khỏi database.
     */
    public function deleteImage(int $imageId): bool
    {
        $stmt = $this->db->prepare("
            SELECT image_path
            FROM room_images
            WHERE id = ?
        ");

        $stmt->execute([$imageId]);

        $path = $stmt->fetchColumn();

        if ($path === false) {
            return false;
        }

        // Không xóa file trên máy chủ bên ngoài.
        if (
            is_string($path)
            && $path !== ''
            && !$this->isExternalImageUrl($path)
        ) {
            $uploadDirectory = realpath(
                __DIR__ . '/../../public/uploads'
            );

            if ($uploadDirectory !== false) {
                $fullPath = realpath(
                    $uploadDirectory . DIRECTORY_SEPARATOR . $path
                );

                // Chỉ cho phép xóa file nằm trực tiếp trong uploads.
                if (
                    $fullPath !== false
                    && is_file($fullPath)
                    && dirname($fullPath) === $uploadDirectory
                ) {
                    @unlink($fullPath);
                }
            }
        }

        $stmt = $this->db->prepare("
            DELETE FROM room_images
            WHERE id = ?
        ");

        return $stmt->execute([$imageId]);
    }

    /**
     * Đặt ảnh đại diện chính.
     */
    public function setPrimaryImage(int $roomId, int $imageId): void
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT id
                FROM room_images
                WHERE id = ? AND room_id = ?
            ");

            $stmt->execute([$imageId, $roomId]);

            if (!$stmt->fetchColumn()) {
                throw new InvalidArgumentException(
                    'Ảnh không thuộc phòng này.'
                );
            }

            $this->db->prepare("
                UPDATE room_images
                SET is_primary = 0
                WHERE room_id = ?
            ")->execute([$roomId]);

            $this->db->prepare("
                UPDATE room_images
                SET is_primary = 1
                WHERE id = ? AND room_id = ?
            ")->execute([$imageId, $roomId]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Kiểm tra phòng có hợp đồng đang hoạt động hay không.
     */
    public function hasActiveContract(int $roomId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM contracts
            WHERE room_id = ? AND status = 'active'
        ");

        $stmt->execute([$roomId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Cập nhật trạng thái phòng.
     */
    public function updateStatus(int $roomId, string $status): bool
    {
        return $this->update($roomId, [
            'status' => $status
        ]);
    }
}