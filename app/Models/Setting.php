<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Setting extends Model
{
    protected string $table = 'system_settings';

    public function getAll(): array
    {
        $rows = $this->all('id ASC');
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    public function set(string $key, ?string $value, ?string $description = null): void
    {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE setting_key = ?");
        $stmt->execute([$key]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $updateData = ['setting_value' => $value];
            if ($description !== null) {
                $updateData['description'] = $description;
            }
            $this->update($id, $updateData);
        } else {
            $this->create([
                'setting_key'   => $key,
                'setting_value' => $value,
                'description'   => $description ?? ''
            ]);
        }
    }
}
