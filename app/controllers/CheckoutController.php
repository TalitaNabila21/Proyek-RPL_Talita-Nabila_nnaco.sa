<?php

require_once '../app/services/ProductRecommendationService.php';

class CheckoutController extends Controller {
    public function __construct() {
        $this->requireRole(['customer']);
    }

    public function index() {
        if (empty($_SESSION['cart'])) {
            header("Location: " . BASEURL . "/ProductController");
            exit;
        }

        $data['judul'] = 'Checkout';
        $data['cart'] = $_SESSION['cart'];
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $total = 0;
        foreach ($data['cart'] as $item) {
            $total += $item['subtotal'];
        }
        $data['total'] = $total;

        // Fetch User detail for default form values if logged in
        $data['user'] = null;
        if (isset($_SESSION['user_id'])) {
            $data['user'] = $this->model('User')->getUserById($_SESSION['user_id']);
        } elseif (isset($_SESSION['user_name'])) {
            $data['user'] = $this->model('User')->getUserByEmail($_SESSION['user_name']); 
        }
        
        $this->view('layouts/main', [
            'view' => 'checkout/index',
            'data' => $data
        ]);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);

            if (empty($_SESSION['cart'])) {
                header("Location: " . BASEURL . "/ProductController");
                exit;
            }

            // Calculate total again from session to prevent tampering
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['subtotal'];
            }

            $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

            $orderData = [
                'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
                'order_number' => $order_number,
                'total_amount' => $total,
                'shipping_name' => htmlspecialchars($_POST['shipping_name']),
                'shipping_phone' => htmlspecialchars($_POST['shipping_phone']),
                'shipping_address' => htmlspecialchars($_POST['shipping_address']),
                'payment_method' => htmlspecialchars($_POST['payment_method'] ?? 'cash')
            ];

            // 1. Simpan pesanan & kurangi stok dalam transaksi database
            $order_id = $this->model('Order')->createOrder($orderData, $_SESSION['cart']);

            if ($order_id) {
                // 2. Kosongkan keranjang belanja
                unset($_SESSION['cart']);
                
                // 3. Kirim notifikasi WhatsApp otomatis ke Owner & konfirmasi ke Customer
                // HANYA setelah transaksi database berhasil dikomit!
                try {
                    require_once '../app/services/OrderNotificationService.php';
                    $notifService = new OrderNotificationService();
                    $notifService->notifyNewOrder((int)$order_id);
                } catch (Exception $e) {
                    // Jika ada error jaringan WhatsApp/Fonnte, pesanan tetap tersimpan aman dan tidak dibatalkan
                    error_log("[CheckoutController] Gagal memicu notifikasi pesanan {$order_number}: " . $e->getMessage());
                }
                
                header("Location: " . BASEURL . "/CheckoutController/success/" . $order_number);
                exit;
            } else {
                // Error message already set in Order model
                header("Location: " . BASEURL . "/CheckoutController");
                exit;
            }
        }
    }

    public function success($order_number) {
        $data['judul'] = 'Order Berhasil';
        $data['order_number'] = $order_number;
        
        $recommender = new ProductRecommendationService();
        $data['recommendations'] = $recommender->getRecommendations([
            'limit' => 4
        ]);
        
        $this->view('layouts/main', [
            'view' => 'checkout/success',
            'data' => $data
        ]);
    }
}
