<?php

class OperatorController extends Controller {
    public function __construct() {
        $this->requireRole(['operator']);
    }

    public function index() {
        $data['judul'] = 'Dashboard Operator';
        $data['user_name'] = $_SESSION['user_name'];
        $data['stats'] = $this->model('Order')->getOrderStats();
        
        $this->view('layouts/admin', [
            'view' => 'operator/index',
            'data' => $data
        ]);
    }
}
