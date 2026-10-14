<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Expense extends Model
{
    protected string $table = 'expenses';

    public function allWithFilters(?string $month = null, ?string $category = null): array
    {
        $sql = "
            SELECT e.*, u.fullname AS created_by_name
            FROM expenses e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($month)) {
            $sql .= " AND DATE_FORMAT(e.expense_date, '%Y-%m') = ?";
            $params[] = $month;
        }

        if (!empty($category)) {
            $sql .= " AND e.category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createExpense(array $data): int
    {
        if (empty($data['expense_code'])) {
            $prefix = 'CP-' . date('Ym') . '-';
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM expenses WHERE DATE_FORMAT(created_at, '%Y%m') = ?");
            $stmtCount->execute([date('Ym')]);
            $seq = (int)$stmtCount->fetchColumn() + 1;
            $data['expense_code'] = $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
        }
        return (int)$this->create($data);
    }
}
