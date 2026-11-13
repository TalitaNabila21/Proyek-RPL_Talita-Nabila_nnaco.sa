<?php

class CustomerController extends Controller {
    public function __construct() {
        $this->requireRole(['customer']);
    }

    public function index() {
        $data['judul'] = 'Dashboard Customer';
        $data['user_name'] = $_SESSION['user_name'];
        $userId = $_SESSION['user_id'];
        
        $orderModel = $this->model('Order');
        $orders = $orderModel->getOrdersByUserId($userId);
        
        $totalOrders = count($orders);
        $pendingOrders = 0;
        $completedOrders = 0;
        foreach ($orders as $o) {
            if (in_array($o['status'], ['pending', 'confirmed', 'processing', 'shipped'])) {
                $pendingOrders++;
            } elseif ($o['status'] === 'completed') {
                $completedOrders++;
            }
        }
        
        $data['total_orders'] = $totalOrders;
        $data['pending_orders'] = $pendingOrders;
        $data['completed_orders'] = $completedOrders;
        $data['recent_orders'] = array_slice($orders, 0, 3);
        
        $this->view('layouts/main', [
            'view' => 'customer/index',
            'data' => $data
        ]);
    }

    public function orders() {
        $data['judul'] = 'Riwayat Pesanan';
        $data['orders'] = $this->model('Order')->getOrdersByUserId($_SESSION['user_id']);
        
        $this->view('layouts/main', [
            'view' => 'customer/orders',
            'data' => $data
        ]);
    }

    public function orderDetail($id) {
        $data['order'] = $this->model('Order')->getOrderByIdAndUser($id, $_SESSION['user_id']);
        
        if (!$data['order']) {
            header("Location: " . BASEURL . "/CustomerController/orders");
            exit;
        }

        $data['judul'] = 'Detail Pesanan ' . $data['order']['order_number'];
        $data['items'] = $this->model('Order')->getOrderItems($id);
        
        $this->view('layouts/main', [
            'view' => 'customer/order_detail',
            'data' => $data
        ]);
    }
}
