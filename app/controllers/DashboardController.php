<?php

class DashboardController extends Controller {
    /**
     * Otomatis mengarahkan ke Dashboard sesuai dengan hak akses (Role) user yang sedang login
     * Jika belum login, diarahkan ke form login dengan pesan informatif
     */
    public function index() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
            $role = strtolower(trim($_SESSION['user_role']));
            if ($role === 'admin') {
                header("Location: " . BASEURL . "/admin");
                exit;
            } elseif ($role === 'owner') {
                header("Location: " . BASEURL . "/owner");
                exit;
            } elseif ($role === 'operator') {
                header("Location: " . BASEURL . "/operator");
                exit;
            } elseif ($role === 'customer') {
                header("Location: " . BASEURL . "/customer");
                exit;
            } else {
                header("Location: " . BASEURL);
                exit;
            }
        }

        // Jika belum login, redirect ke halaman login
        $_SESSION['flash_msg'] = 'Silakan login terlebih dahulu untuk mengakses Dashboard.';
        $_SESSION['flash_type'] = 'info';
        header("Location: " . BASEURL . "/auth/login");
        exit;
    }
}
