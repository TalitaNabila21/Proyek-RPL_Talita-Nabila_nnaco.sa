<?php

class OwnerController extends Controller {
    public function __construct() {
        $this->requireRole(['owner']);
    }

    public function index() {
        $data['judul'] = 'Owner Dashboard & Report';
        $data['user_name'] = $_SESSION['user_name'];
        
        // Handle Date Filter
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'this_month';
        $startDate = null;
        $endDate = null;
        $data['filter'] = $filter;
        
        $today = date('Y-m-d');
        
        if ($filter == 'today') {
            $startDate = $today;
            $endDate = $today;
        } elseif ($filter == 'this_week') {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($filter == 'this_month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } elseif ($filter == 'this_year') {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        } elseif ($filter == 'custom') {
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : $today;
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : $today;
        }
        
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        // Fetch Aggregated Data
        $reportModel = $this->model('Report');
        $data['stats'] = $reportModel->getDashboardStats($startDate, $endDate);
        $data['top_products'] = $reportModel->getTopProducts($startDate, $endDate);
        $data['order_status'] = $reportModel->getOrderStatusSummary($startDate, $endDate);
        $data['recent_orders'] = $reportModel->getRecentOrders(5);
        $data['chart_data'] = $reportModel->getRevenuePerPeriod($startDate, $endDate);
        
        // Process Status Summary for UI
        $data['status_count'] = [
            'pending' => 0, 'confirmed' => 0, 'processing' => 0, 'shipped' => 0, 'completed' => 0, 'cancelled' => 0
        ];
        $total_orders_for_progress = 0;
        foreach ($data['order_status'] as $st) {
            $data['status_count'][$st['status']] = $st['total'];
            $total_orders_for_progress += $st['total'];
        }
        $data['total_orders_for_progress'] = $total_orders_for_progress;
        
        $this->view('layouts/admin', [
            'view' => 'owner/index',
            'data' => $data
        ]);
    }
}
