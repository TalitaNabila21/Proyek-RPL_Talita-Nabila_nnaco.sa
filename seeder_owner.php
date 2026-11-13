<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

echo "=== Seeder Owner & Admin Pertama ===\n";

// Seed Owner
$ownerEmail = 'owner@nnaco.com';
$ownerPass = 'rahasia123';
$ownerHash = password_hash($ownerPass, PASSWORD_BCRYPT);

$db->query("SELECT id FROM users WHERE email = :email");
$db->bind('email', $ownerEmail);
$exists = $db->single();

if (!$exists) {
    $db->query("INSERT INTO users (name, email, password, role, status) VALUES ('Owner Utama', :email, :password, 'owner', 'active')");
    $db->bind('email', $ownerEmail);
    $db->bind('password', $ownerHash);
    if($db->execute()) {
        echo "Akun Owner berhasil dibuat! Email: $ownerEmail | Pass: $ownerPass\n";
    }
} else {
    // Update password just in case
    $db->query("UPDATE users SET password = :password, role = 'owner', status = 'active' WHERE email = :email");
    $db->bind('password', $ownerHash);
    $db->bind('email', $ownerEmail);
    $db->execute();
    echo "Akun Owner sudah ada. Password di-reset menjadi: $ownerPass\n";
}

// Seed Admin
$adminEmail = 'admin@nnaco.com';
$adminPass = 'admin123';
$adminHash = password_hash($adminPass, PASSWORD_BCRYPT);

$db->query("SELECT id FROM users WHERE email = :email");
$db->bind('email', $adminEmail);
$existsAdmin = $db->single();

if (!$existsAdmin) {
    $db->query("INSERT INTO users (name, email, password, role, status) VALUES ('Administrator', :email, :password, 'admin', 'active')");
    $db->bind('email', $adminEmail);
    $db->bind('password', $adminHash);
    if($db->execute()) {
        echo "Akun Admin berhasil dibuat! Email: $adminEmail | Pass: $adminPass\n";
    }
} else {
    $db->query("UPDATE users SET password = :password, role = 'admin', status = 'active' WHERE email = :email");
    $db->bind('password', $adminHash);
    $db->bind('email', $adminEmail);
    $db->execute();
    echo "Akun Admin sudah ada. Password di-reset menjadi: $adminPass\n";
}

echo "Seeding selesai.\n";
