<?php

namespace App\Core;

use App\Helpers\Database;
use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy tất cả bản ghi
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Tìm bản ghi theo ID
     */
    public function find(int|string $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Thêm mới một bản ghi
     */
    public function create(array $data): int|string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật bản ghi theo ID
     */
    public function update(int|string $id, array $data): bool
    {
        $setClauses = [];
        $values = [];

        foreach ($data as $col => $val) {
            $setClauses[] = "{$col} = ?";
            $values[] = $val;
        }

        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Xóa bản ghi theo ID
     */
    public function delete(int|string $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Đếm số lượng bản ghi thỏa điều kiện
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
