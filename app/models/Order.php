<?php

class Order {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function createOrder($orderData, $cartItems) {
        try {
            $this->db->beginTransaction();

            // 1. Insert Order
            $this->db->query("INSERT INTO orders (user_id, order_number, total_amount, shipping_name, shipping_phone, shipping_address, payment_method, payment_status) 
                              VALUES (:user_id, :order_number, :total_amount, :shipping_name, :shipping_phone, :shipping_address, :payment_method, :payment_status)");
            $this->db->bind('user_id', $orderData['user_id']);
            $this->db->bind('order_number', $orderData['order_number']);
            $this->db->bind('total_amount', $orderData['total_amount']);
            $this->db->bind('shipping_name', $orderData['shipping_name']);
            $this->db->bind('shipping_phone', $orderData['shipping_phone']);
            $this->db->bind('shipping_address', $orderData['shipping_address']);
            $this->db->bind('payment_method', $orderData['payment_method'] ?? 'cash');
            $this->db->bind('payment_status', 'unpaid'); // Initial status always unpaid
            $this->db->execute();

            // Get last inserted order ID
            $this->db->query("SELECT LAST_INSERT_ID() as order_id");
            $order_id = $this->db->single()['order_id'];

            // 2. Insert Order Items and Deduct Stock
            foreach ($cartItems as $item) {
                // Double check stock before processing
                $this->db->query("SELECT stock FROM products WHERE id = :id FOR UPDATE");
                $this->db->bind('id', $item['id']);
                $product = $this->db->single();
                
                if (!$product || $product['stock'] < $item['quantity']) {
                    throw new Exception("Stok untuk produk " . $item['name'] . " tidak mencukupi.");
                }

                // Insert Item
                $this->db->query("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                                  VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)");
                $this->db->bind('order_id', $order_id);
                $this->db->bind('product_id', $item['id']);
                $this->db->bind('product_name', $item['name']);
                $this->db->bind('price', $item['price']);
                $this->db->bind('quantity', $item['quantity']);
                $this->db->bind('subtotal', $item['subtotal']);
                $this->db->execute();

                // Deduct stock
                $this->db->query("UPDATE products SET stock = stock - :quantity WHERE id = :id");
                $this->db->bind('quantity', $item['quantity']);
                $this->db->bind('id', $item['id']);
                $this->db->execute();
            }

            $this->db->commit();
            return $order_id;

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash_msg'] = $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
            return false;
        }
    }

    public function getOrdersByUserId($user_id) {
        $this->db->query("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $this->db->bind('user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getOrderByIdAndUser($order_id, $user_id) {
        $this->db->query("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
        $this->db->bind('order_id', $order_id);
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }

    public function getOrderItems($order_id) {
        $this->db->query("SELECT * FROM order_items WHERE order_id = :order_id");
        $this->db->bind('order_id', $order_id);
        return $this->db->resultSet();
    }

    public function getAllOrders($status = '', $search = '') {
        $query = "SELECT orders.*, users.name as customer_name, users.email as customer_email 
                  FROM orders 
                  LEFT JOIN users ON orders.user_id = users.id ";
        
        $conditions = [];
        if ($status != '') {
            $conditions[] = "orders.status = :status";
        }
        if ($search != '') {
            $conditions[] = "(orders.order_number LIKE :search OR users.name LIKE :search)";
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY orders.created_at DESC";

        $this->db->query($query);
        
        if ($status != '') {
            $this->db->bind('status', $status);
        }
        if ($search != '') {
            $this->db->bind('search', "%$search%");
        }
        
        return $this->db->resultSet();
    }

    public function getOrderById($id) {
        $this->db->query("SELECT orders.*, users.name as customer_name, users.email as customer_email 
                          FROM orders 
                          LEFT JOIN users ON orders.user_id = users.id 
                          WHERE orders.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getOrderByNumber($order_number) {
        $this->db->query("SELECT orders.*, users.name as customer_name, users.email as customer_email 
                          FROM orders 
                          LEFT JOIN users ON orders.user_id = users.id 
                          WHERE orders.order_number = :order_number");
        $this->db->bind('order_number', $order_number);
        return $this->db->single();
    }

    public function updateStatus($id, $status, $payment_status = null, $courier = null, $tracking = null) {
        $query = "UPDATE orders SET status = :status";
        
        if ($payment_status) {
            $query .= ", payment_status = :payment_status";
        }
        
        if ($status == 'shipped') {
            $query .= ", shipping_courier = :courier, tracking_number = :tracking, shipped_at = CURRENT_TIMESTAMP";
        } elseif ($status == 'completed') {
            $query .= ", completed_at = CURRENT_TIMESTAMP";
        }

        $query .= " WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        
        if ($payment_status) {
            $this->db->bind('payment_status', $payment_status);
        }
        
        if ($status == 'shipped') {
            $this->db->bind('courier', $courier);
            $this->db->bind('tracking', $tracking);
        }
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getOrderStats() {
        $this->db->query("SELECT 
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending,
                            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as total_processing,
                            SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as total_shipped,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as total_completed
                          FROM orders");
        return $this->db->single();
    }

    public function getTotalOrdersCount() {
        $this->db->query("SELECT COUNT(*) as total FROM orders");
        return (int)($this->db->single()['total'] ?? 0);
    }
}
