<?php
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $pdo = \App\Helpers\Database::getConnection();
    echo "=== USERS ===\n";
    $users = $pdo->query("SELECT id, username, role, fullname, phone, status FROM users")->fetchAll();
    print_r($users);
    
    echo "\n=== ROOMS COUNT ===\n";
    $rooms = $pdo->query("SELECT count(*) as total, status FROM rooms GROUP BY status")->fetchAll();
    print_r($rooms);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
