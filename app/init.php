<?php

date_default_timezone_set('Asia/Jakarta');

require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/Helpers.php';
require_once 'services/FonnteService.php';

require_once 'core/DotEnv.php';

require_once 'config/config.php';

// Initialize DotEnv
$dotenv = new DotEnv(dirname(__DIR__) . '/.env');
$dotenv->load();

// Sinkronisasi Sesi Pengguna: Otomatis sinkronkan user_role dan user_name jika ada user_id aktif
if (session_id() && isset($_SESSION['user_id']) && (empty($_SESSION['user_role']) || empty($_SESSION['user_name']))) {
    try {
        $initDb = new Database();
        $initDb->query("SELECT id, name, role, status FROM users WHERE id = :id LIMIT 1");
        $initDb->bind('id', $_SESSION['user_id']);
        $initUser = $initDb->single();
        if ($initUser && ($initUser['status'] ?? 'active') === 'active') {
            $_SESSION['user_role'] = $initUser['role'];
            if (empty($_SESSION['user_name']) && !empty($initUser['name'])) {
                $_SESSION['user_name'] = $initUser['name'];
            }
        } else {
            // Sesi kadaluarsa / akun tidak ditemukan di database
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
        }
    } catch (\Throwable $e) {
        // Fallback jika database belum siap
    }
}
