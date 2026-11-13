<?php

require_once '../app/services/FonnteService.php';
require_once '../app/services/OrderNotificationService.php';

class NotificationController extends Controller {

    public function __construct() {
        // Hanya Admin dan Owner yang memiliki hak akses ke log notifikasi
        $this->requireRole(['admin', 'owner']);
    }

    /**
     * Menampilkan halaman riwayat log notifikasi WhatsApp dan pengaturan (khusus Owner)
     */
    public function index() {
        $data['judul'] = 'Log & Pengaturan Notifikasi WhatsApp';
        $data['user_role'] = $_SESSION['user_role'] ?? '';

        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $logModel = $this->model('NotificationLog');
        $settingModel = $this->model('Setting');

        $data['logs'] = $logModel->getAllLogs($limit, $offset, $status);
        $data['total_logs'] = $logModel->countLogs($status);
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($data['total_logs'] / $limit);
        $data['filter_status'] = $status;

        // Data statistik notifikasi
        $data['count_all'] = $logModel->countLogs('');
        $data['count_sent'] = $logModel->countLogs('sent');
        $data['count_failed'] = $logModel->countLogs('failed');
        $data['count_pending'] = $logModel->countLogs('pending');

        // Pengaturan (Hanya diambil/diubah jika role Owner)
        $data['settings'] = $settingModel->getAllSettings();
        
        // Status perangkat Fonnte
        $fonnte = new FonnteService();
        $data['is_fonnte_configured'] = $fonnte->isConfigured();
        $data['device_details'] = $fonnte->getDeviceDetails();
        $data['connected_device'] = $fonnte->getConnectedDevice();

        $data['csrf_token'] = $this->generateCsrfToken();

        $this->view('layouts/admin', [
            'view' => 'notification/index',
            'data' => $data
        ]);
    }

    /**
     * Menyimpan pengaturan nomor penerima notifikasi WhatsApp (Khusus Owner)
     */
    public function saveSettings() {
        // Kontrol akses ketat: Hanya role 'owner' yang berhak mengubah konfigurasi toko
        $this->requireRole(['owner']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/NotificationController");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $orderPhone = trim($_POST['order_notification_phone'] ?? '');
        $notifyOwner = isset($_POST['order_notify_owner']) ? '1' : '0';
        $notifyCustomer = isset($_POST['order_notify_customer']) ? '1' : '0';
        $notifyAdmin = isset($_POST['order_notify_admin']) ? '1' : '0';
        $adminPhones = trim($_POST['admin_notification_phones'] ?? '');

        // Normalisasi nomor Owner jika diisi
        if (!empty($orderPhone)) {
            $normalized = FonnteService::normalizePhoneNumber($orderPhone);
            if (!$normalized) {
                $_SESSION['flash_msg'] = 'Format nomor WhatsApp Owner/Toko tidak valid. Gunakan format seperti 081234567890 atau 6281234567890.';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/NotificationController");
                exit;
            }
            $orderPhone = $normalized;
        }

        $settingModel = $this->model('Setting');
        $settingModel->updateSetting('order_notification_phone', $orderPhone);
        $settingModel->updateSetting('order_notify_owner', $notifyOwner);
        $settingModel->updateSetting('order_notify_customer', $notifyCustomer);
        $settingModel->updateSetting('order_notify_admin', $notifyAdmin);
        $settingModel->updateSetting('admin_notification_phones', $adminPhones);

        $_SESSION['flash_msg'] = 'Pengaturan notifikasi WhatsApp berhasil disimpan!';
        $_SESSION['flash_type'] = 'success';
        header("Location: " . BASEURL . "/NotificationController");
        exit;
    }

    /**
     * Mencoba mengirim ulang notifikasi yang gagal (Retry)
     */
    public function retry() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/NotificationController");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $logId = (int)($_POST['log_id'] ?? 0);
        if ($logId <= 0) {
            $_SESSION['flash_msg'] = 'ID log notifikasi tidak valid.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/NotificationController");
            exit;
        }

        $notifService = new OrderNotificationService();
        $res = $notifService->retryNotification($logId);

        if ($res['success']) {
            $_SESSION['flash_msg'] = 'Berhasil! ' . $res['message'];
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal mengirim ulang: ' . $res['message'];
            $_SESSION['flash_type'] = 'danger';
        }

        $redirectUrl = isset($_POST['redirect_back']) ? $_POST['redirect_back'] : (BASEURL . "/NotificationController");
        header("Location: " . $redirectUrl);
        exit;
    }

    /**
     * Menguji coba kirim pesan tes ke nomor WhatsApp Owner yang tersimpan
     */
    public function testSend() {
        $this->requireRole(['owner']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/NotificationController");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $settingModel = $this->model('Setting');
        $phone = $settingModel->getSetting('order_notification_phone');
        if (empty($phone)) {
            $phone = getenv('ORDER_NOTIFICATION_PHONE');
        }

        if (empty($phone)) {
            $_SESSION['flash_msg'] = 'Nomor WhatsApp Owner/Toko belum diisi. Silakan isi dan simpan nomor terlebih dahulu.';
            $_SESSION['flash_type'] = 'warning';
            header("Location: " . BASEURL . "/NotificationController");
            exit;
        }

        $normalized = FonnteService::normalizePhoneNumber($phone);
        $fonnte = new FonnteService();

        $testMsg = "Halo Owner nnaco_sa! Ini adalah pesan uji coba dari sistem notifikasi WhatsApp toko Anda. Fitur notifikasi otomatis siap beroperasi!";
        $sendRes = $fonnte->sendWhatsApp($normalized, $testMsg);

        if ($sendRes['success']) {
            $_SESSION['flash_msg'] = "Pesan uji coba berhasil dikirim ke nomor WhatsApp {$normalized}!";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = "Gagal mengirim pesan uji coba: " . ($sendRes['message'] ?? 'Kesalahan tidak diketahui.');
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/NotificationController");
        exit;
    }

    /**
     * Endpoint AJAX untuk memuat QR Code Fonnte terkini (Admin & Owner)
     */
    public function fonnteQr() {
        $fonnte = new FonnteService();
        $qrData = $fonnte->getQrCode();
        $details = $fonnte->getDeviceDetails();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => $qrData['status'] ?? false,
            'qr_image' => !empty($qrData['url']) ? 'data:image/png;base64,' . $qrData['url'] : null,
            'connected' => ($details['device_status'] ?? '') === 'connect',
            'device' => $details['device'] ?? null,
            'device_status' => $details['device_status'] ?? 'disconnect',
            'name' => $details['name'] ?? '',
            'quota' => $details['quota'] ?? 0
        ]);
        exit;
    }

    /**
     * Endpoint AJAX untuk mengecek status perangkat Fonnte real-time
     */
    public function checkStatus() {
        $fonnte = new FonnteService();
        $details = $fonnte->getDeviceDetails();
        $isConnected = ($details['device_status'] ?? '') === 'connect';

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'connected' => $isConnected,
            'device' => $details['device'] ?? null,
            'device_status' => $details['device_status'] ?? 'disconnect',
            'quota' => $details['quota'] ?? 0,
            'name' => $details['name'] ?? '',
            'expired' => $details['expired'] ?? ''
        ]);
        exit;
    }

    /**
     * Memutuskan koneksi perangkat Fonnte agar bisa pairing dengan nomor lain (Khusus Owner)
     */
    public function disconnectDevice() {
        $this->requireRole(['owner']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/NotificationController");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $fonnte = new FonnteService();
        $res = $fonnte->disconnectDevice();

        if (!empty($res['status'])) {
            $_SESSION['flash_msg'] = 'Koneksi perangkat WhatsApp berhasil diputus dari Fonnte. Anda dapat men-scan QR baru.';
            $_SESSION['flash_type'] = 'info';
        } else {
            $_SESSION['flash_msg'] = 'Gagal memutuskan perangkat: ' . ($res['message'] ?? $res['detail'] ?? 'Kesalahan tidak diketahui.');
            $_SESSION['flash_type'] = 'warning';
        }

        header("Location: " . BASEURL . "/NotificationController");
        exit;
    }
}

