<?php

class AdminOrderController extends Controller {
    public function __construct() {
        $this->requireRole(['admin']);
    }

    public function index() {
        $data['judul'] = 'Semua Pesanan';
        
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $data['orders'] = $this->model('Order')->getAllOrders($status, $search);
        $data['status'] = $status;
        $data['search'] = $search;

        $this->view('layouts/admin', [
            'view' => 'admin_order/index',
            'data' => $data
        ]);
    }

    public function detail($id) {
        $data['order'] = $this->model('Order')->getOrderById($id);
        
        if (!$data['order']) {
            header("Location: " . BASEURL . "/AdminOrderController");
            exit;
        }

        $data['judul'] = 'Detail Pesanan ' . $data['order']['order_number'];
        $data['items'] = $this->model('Order')->getOrderItems($id);
        $data['notifications'] = $this->model('NotificationLog')->getLogsByOrderId((int)$id);
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $this->view('layouts/admin', [
            'view' => 'admin_order/detail',
            'data' => $data
        ]);
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            $id = $_POST['order_id'];
            $new_status = $_POST['status'];
            $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : null;
            $courier = isset($_POST['shipping_courier']) ? htmlspecialchars($_POST['shipping_courier']) : null;
            $tracking = isset($_POST['tracking_number']) ? htmlspecialchars($_POST['tracking_number']) : null;

            $order = $this->model('Order')->getOrderById($id);
            if (!$order) {
                header("Location: " . BASEURL . "/AdminOrderController");
                exit;
            }

            // Aturan Bisnis: Tidak bisa cancel jika sudah shipped/completed
            if ($new_status == 'cancelled' && in_array($order['status'], ['shipped', 'completed'])) {
                $_SESSION['flash_msg'] = 'Pesanan yang sudah dikirim tidak dapat dibatalkan.';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/AdminOrderController/detail/" . $id);
                exit;
            }

            // Validasi Input Resi
            if ($new_status == 'shipped') {
                if (empty($courier) || empty($tracking)) {
                    $_SESSION['flash_msg'] = 'Nama kurir dan Nomor Resi wajib diisi untuk status Shipped.';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/AdminOrderController/detail/" . $id);
                    exit;
                }
            }

            if ($this->model('Order')->updateStatus($id, $new_status, $payment_status, $courier, $tracking) > 0) {
                $_SESSION['flash_msg'] = 'Status pesanan berhasil diperbarui.';
                $_SESSION['flash_type'] = 'success';

                // Kirim notifikasi WhatsApp ke Customer jika status benar-benar berubah
                if ($order['status'] !== $new_status) {
                    try {
                        require_once '../app/services/OrderNotificationService.php';
                        $notifService = new OrderNotificationService();
                        $notifService->notifyStatusUpdate((int)$id, $new_status);
                    } catch (Exception $e) {
                        error_log("[AdminOrderController] Gagal notifikasi status update order {$id}: " . $e->getMessage());
                    }
                }
            } else {
                $_SESSION['flash_msg'] = 'Tidak ada perubahan atau gagal memperbarui.';
                $_SESSION['flash_type'] = 'warning';
            }
            
            header("Location: " . BASEURL . "/AdminOrderController/detail/" . $id);
            exit;
        }
    }
}
