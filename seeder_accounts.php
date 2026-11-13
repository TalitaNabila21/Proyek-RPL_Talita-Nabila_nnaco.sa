<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

echo "=== Memperbarui Akun Demo ===\n";

$accounts = [
    [
        'name' => 'Owner nnaco_sa',
        'email' => 'owner@nnaco.com',
        'password' => 'owner123',
        'role' => 'owner'
    ],
    [
        'name' => 'Admin nnaco_sa',
        'email' => 'admin@nnaco.com',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'name' => 'Operator 1',
        'email' => 'operator@nnaco.com',
        'password' => 'operator123',
        'role' => 'operator'
    ],
    [
        'name' => 'Customer Setia',
        'email' => 'customer@nnaco.com',
        'password' => 'customer123',
        'role' => 'customer'
    ]
];

foreach ($accounts as $acc) {
    $hash = password_hash($acc['password'], PASSWORD_BCRYPT);
    
    // Check if exists
    $db->query("SELECT id FROM users WHERE email = :email");
    $db->bind('email', $acc['email']);
    $exists = $db->single();
    
    if (!$exists) {
        $db->query("INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, :role, 'active')");
        $db->bind('name', $acc['name']);
        $db->bind('email', $acc['email']);
        $db->bind('password', $hash);
        $db->bind('role', $acc['role']);
        $db->execute();
        echo "Akun " . $acc['role'] . " berhasil dibuat!\n";
    } else {
        $db->query("UPDATE users SET name = :name, password = :password, role = :role, status = 'active' WHERE email = :email");
        $db->bind('name', $acc['name']);
        $db->bind('password', $hash);
        $db->bind('role', $acc['role']);
        $db->bind('email', $acc['email']);
        $db->execute();
        echo "Akun " . $acc['role'] . " diperbarui dengan password standar!\n";
    }
}

echo "Selesai.\n";
