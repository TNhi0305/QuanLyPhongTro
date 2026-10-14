<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Service extends Model
{
    protected string $table = 'services';

    public function allActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY type ASC, id ASC");
        return $stmt->fetchAll();
    }

    public function getElectricityRate(): float
    {
        $stmt = $this->db->prepare("SELECT price FROM {$this->table} WHERE service_code = 'DV-DIEN' LIMIT 1");
        $stmt->execute();
        $rate = $stmt->fetchColumn();
        return $rate ? (float)$rate : 3500.0;
    }

    public function getWaterRate(): float
    {
        $stmt = $this->db->prepare("SELECT price FROM {$this->table} WHERE service_code = 'DV-NUOC' LIMIT 1");
        $stmt->execute();
        $rate = $stmt->fetchColumn();
        return $rate ? (float)$rate : 20000.0;
    }

    public function getFixedServices(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE type = 'fixed' AND is_active = 1");
        return $stmt->fetchAll();
    }
}
