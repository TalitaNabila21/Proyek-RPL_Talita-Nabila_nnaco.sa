<?php

class Controller {

    public function view($view, $data = []) {
        require_once '../app/views/' . $view . '.php';
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }

    // Role-based access control
    public function requireRole($roles = []) {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_msg'] = 'Anda harus login untuk mengakses halaman tersebut.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        // Jika array $roles tidak kosong, cek apakah role user ada di dalamnya
        if (!empty($roles) && !in_array($_SESSION['user_role'], $roles)) {
            $_SESSION['flash_msg'] = 'Anda tidak memiliki akses ke halaman tersebut.';
            $_SESSION['flash_type'] = 'danger';
            
            // Redirect back to their respective dashboard
            $role = $_SESSION['user_role'];
            header("Location: " . BASEURL . "/" . $role);
            exit;
        }
    }

    // CSRF Protection
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            $_SESSION['flash_msg'] = 'Invalid security token. Silakan coba lagi.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASEURL));
            exit;
        }
    }
}
