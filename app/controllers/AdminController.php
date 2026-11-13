<?php

class AdminController extends Controller {
    public function __construct() {
        $this->requireRole(['admin']);
    }

    public function index() {
        $data['judul'] = 'Dashboard Admin';
        $data['user_name'] = $_SESSION['user_name'];
        
        $userModel = $this->model('User');
        $orderModel = $this->model('Order');

        $userStats = $userModel->getUserStats();
        $orderStats = $orderModel->getOrderStats();

        // Total pengguna aktif / yang bisa login
        $data['total_users'] = $userStats['can_login_total'] ?? $userStats['total'];
        $data['user_stats'] = $userStats;
        $data['pending_orders'] = $orderStats['total_pending'] ?? 0;
        
        $this->view('layouts/admin', [
            'view' => 'admin/index',
            'data' => $data
        ]);
    }
}
