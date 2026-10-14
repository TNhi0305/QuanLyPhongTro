<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    public function getRecent(int $limit = 50): array
    {
        $sql = "
            SELECT l.*, u.fullname AS user_name, u.username
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC
            LIMIT {$limit}
        ";
        return $this->db->query($sql)->fetchAll();
    }
}
