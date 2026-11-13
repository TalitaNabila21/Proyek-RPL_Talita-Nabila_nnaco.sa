<?php

class Report {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    private function getDateCondition($startDate, $endDate, $tableAlias = 'orders') {
        if ($startDate && $endDate) {
            return " AND DATE($tableAlias.created_at) BETWEEN :start_date AND :end_date ";
        }
        return "";
    }

    private function bindDates($startDate, $endDate) {
        if ($startDate && $endDate) {
            $this->db->bind('start_date', $startDate);
            $this->db->bind('end_date', $endDate);
        }
    }

    public function getDashboardStats($startDate = null, $endDate = null) {
        $dateCond = $this->getDateCondition($startDate, $endDate);
        
        $query = "SELECT 
                    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled' $dateCond) as total_revenue,
                    (SELECT COUNT(id) FROM orders WHERE 1=1 $dateCond) as total_orders,
                    (SELECT COUNT(id) FROM products WHERE status = 'active') as total_products,
                    (SELECT COUNT(id) FROM users WHERE role = 'customer') as total_customers";
                    
        $this->db->query($query);
        
        // Bind dates for orders count
        if ($startDate && $endDate) {
            $this->db->bind('start_date', $startDate);
            $this->db->bind('end_date', $endDate);
        }
        
        return $this->db->single();
    }

    public function getTopProducts($startDate = null, $endDate = null, $limit = 5) {
        $dateCond = $this->getDateCondition($startDate, $endDate, 'orders');
        
        $query = "SELECT 
                    oi.product_name, 
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.subtotal) as total_revenue
                  FROM order_items oi
                  JOIN orders ON oi.order_id = orders.id
                  WHERE orders.status != 'cancelled' $dateCond
                  GROUP BY oi.product_id, oi.product_name
                  ORDER BY total_sold DESC
                  LIMIT :limit";
                  
        $this->db->query($query);
        $this->bindDates($startDate, $endDate);
        $this->db->bind('limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getOrderStatusSummary($startDate = null, $endDate = null) {
        $dateCond = $this->getDateCondition($startDate, $endDate);
        
        $query = "SELECT status, COUNT(id) as total
                  FROM orders 
                  WHERE 1=1 $dateCond
                  GROUP BY status";
                  
        $this->db->query($query);
        $this->bindDates($startDate, $endDate);
        
        return $this->db->resultSet();
    }

    public function getRecentOrders($limit = 5) {
        $query = "SELECT orders.*, users.name as customer_name 
                  FROM orders 
                  JOIN users ON orders.user_id = users.id 
                  ORDER BY orders.created_at DESC 
                  LIMIT :limit";
                  
        $this->db->query($query);
        $this->db->bind('limit', $limit);
        
        return $this->db->resultSet();
    }

    public function getRevenuePerPeriod($startDate = null, $endDate = null) {
        $dateCond = $this->getDateCondition($startDate, $endDate);
        
        // Default: group by date
        $query = "SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue
                  FROM orders
                  WHERE status != 'cancelled' $dateCond
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
                  
        $this->db->query($query);
        $this->bindDates($startDate, $endDate);
        
        return $this->db->resultSet();
    }
}
