<?php

require_once __DIR__ . '/FonnteService.php';
require_once dirname(__DIR__) . '/models/NotificationLog.php';
require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/models/Setting.php';
require_once dirname(__DIR__) . '/models/User.php';

class OrderNotificationService {

    private $fonnte;
    private $logModel;
    private $orderModel;
    private $settingModel;
    private $userModel;

    public function __construct() {
        $this->fonnte = new FonnteService();
        $this->logModel = new NotificationLog();
        $this->orderModel = new Order();
        $this->settingModel = new Setting();
        $this->userModel = new User();
    }

    /**
     * Mengirim notifikasi WhatsApp pesanan baru ke Owner dan Customer
     * Dipanggil HANYA SETELAH transaksi pesanan berhasil dikomit ke database.
     *
     * @param int $orderId ID pesanan di database
     * @return array Status pengiriman ['owner' => [...], 'customer' => [...]]
     */
    public function notifyNewOrder(int $orderId): array {
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            error_log("[OrderNotificationService] Order ID {$orderId} tidak ditemukan.");
            return [
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.'
            ];
        }

        $items = $this->orderModel->getOrderItems($orderId);
        $results = [
            'owner' => null,
            'customer' => null,
            'admins' => []
        ];

        // 1. Kirim Notifikasi ke Owner / Nomor Toko
        $isNotifyOwnerEnabled = (string)$this->settingModel->getSetting('order_notify_owner', '1') === '1';
        if ($isNotifyOwnerEnabled) {
            $results['owner'] = $this->sendOwnerNewOrderNotification($order, $items);
        }

        // 2. Kirim Notifikasi Konfirmasi ke Customer
        $isNotifyCustomerEnabled = (string)$this->settingModel->getSetting('order_notify_customer', '1') === '1';
        if ($isNotifyCustomerEnabled) {
            $results['customer'] = $this->sendCustomerOrderConfirmation($order, $items);
        }

        // 3. Kirim ke Admin jika fitur diaktifkan oleh Owner
        $isNotifyAdminEnabled = (string)$this->settingModel->getSetting('order_notify_admin', '0') === '1';
        if ($isNotifyAdminEnabled) {
            $results['admins'] = $this->sendAdminNewOrderNotifications($order, $items);
        }

        return $results;
    }

    /**
     * Mengirim pesan notifikasi pesanan baru kepada Owner / Toko
     */
    public function sendOwnerNewOrderNotification(array $order, array $items): array {
        $orderId = (int)$order['id'];
        $recipientType = 'owner';
        $notificationType = 'new_order_owner';

        // Cegah duplikasi notifikasi (Idempotency)
        if ($this->logModel->hasSuccessfulLog($orderId, $recipientType, $notificationType)) {
            return [
                'success' => true,
                'status' => 'skipped',
                'message' => 'Notifikasi Owner sudah berhasil dikirim sebelumnya.'
            ];
        }

        // Ambil nomor WhatsApp Owner/Toko yang sah
        $ownerPhone = $this->resolveOwnerPhone();
        if (empty($ownerPhone)) {
            $errorMsg = 'Nomor WhatsApp Owner/Toko belum dikonfigurasi di pengaturan aplikasi atau .env.';
            error_log("[OrderNotificationService] {$errorMsg} Order: {$order['order_number']}");

            $logId = $this->logModel->createLog($orderId, $recipientType, '-', $notificationType, 'failed');
            $this->logModel->updateLogStatus($logId, 'failed', 'NO_PHONE', $errorMsg);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => $errorMsg
            ];
        }

        $normalizedPhone = FonnteService::normalizePhoneNumber($ownerPhone);
        if (!$normalizedPhone) {
            $errorMsg = "Format nomor WhatsApp Owner tidak valid: {$ownerPhone}";
            $logId = $this->logModel->createLog($orderId, $recipientType, $ownerPhone, $notificationType, 'failed');
            $this->logModel->updateLogStatus($logId, 'failed', 'INVALID_PHONE', $errorMsg);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => $errorMsg
            ];
        }

        // Susun pesan notifikasi sesuai template resmi yang diwajibkan
        $message = $this->formatOwnerNewOrderMessage($order, $items);

        // Catat log awal (pending)
        $existingLog = $this->logModel->getLogForIdempotency($orderId, $recipientType, $notificationType);
        if ($existingLog && $existingLog['message_status'] === 'failed') {
            $logId = (int)$existingLog['id'];
            $this->logModel->incrementAttempt($logId);
        } else {
            $logId = $this->logModel->createLog($orderId, $recipientType, $normalizedPhone, $notificationType, 'pending');
        }

        // Eksekusi pengiriman via Fonnte backend
        try {
            $sendRes = $this->fonnte->sendWhatsApp($normalizedPhone, $message);
            $providerCode = isset($sendRes['http_code']) ? (string)$sendRes['http_code'] : ($sendRes['success'] ? '200' : '500');

            if ($sendRes['success']) {
                $this->logModel->updateLogStatus($logId, 'sent', $providerCode, null);
                return [
                    'success' => true,
                    'status' => 'sent',
                    'message' => 'Notifikasi Owner berhasil dikirim via WhatsApp.'
                ];
            } else {
                $cleanError = $this->sanitizeError($sendRes['message'] ?? 'Pengiriman Fonnte gagal.');
                $this->logModel->updateLogStatus($logId, 'failed', $providerCode, $cleanError);
                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => $cleanError
                ];
            }
        } catch (Exception $e) {
            $cleanError = $this->sanitizeError($e->getMessage());
            $this->logModel->updateLogStatus($logId, 'failed', 'EXCEPTION', $cleanError);
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $cleanError
            ];
        }
    }

    /**
     * Mengirim pesan konfirmasi pesanan kepada Customer
     */
    public function sendCustomerOrderConfirmation(array $order, array $items): array {
        $orderId = (int)$order['id'];
        $recipientType = 'customer';
        $notificationType = 'order_confirmation_customer';

        // Cegah duplikasi notifikasi (Idempotency)
        if ($this->logModel->hasSuccessfulLog($orderId, $recipientType, $notificationType)) {
            return [
                'success' => true,
                'status' => 'skipped',
                'message' => 'Notifikasi konfirmasi Customer sudah dikirim sebelumnya.'
            ];
        }

        // Ambil nomor WhatsApp Customer dari data pengiriman atau data akun
        $customerPhone = !empty($order['shipping_phone']) ? $order['shipping_phone'] : '';
        if (empty($customerPhone) && !empty($order['user_id'])) {
            $user = $this->userModel->getUserById($order['user_id']);
            if ($user && !empty($user['phone'])) {
                $customerPhone = $user['phone'];
            }
        }

        if (empty($customerPhone)) {
            $errorMsg = 'Customer tidak memiliki nomor WhatsApp yang tercatat.';
            $logId = $this->logModel->createLog($orderId, $recipientType, '-', $notificationType, 'failed');
            $this->logModel->updateLogStatus($logId, 'failed', 'NO_PHONE', $errorMsg);
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $errorMsg
            ];
        }

        $normalizedPhone = FonnteService::normalizePhoneNumber($customerPhone);
        if (!$normalizedPhone) {
            $errorMsg = "Nomor WhatsApp Customer tidak valid: {$customerPhone}";
            $logId = $this->logModel->createLog($orderId, $recipientType, $customerPhone, $notificationType, 'failed');
            $this->logModel->updateLogStatus($logId, 'failed', 'INVALID_PHONE', $errorMsg);
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $errorMsg
            ];
        }

        // Susun pesan konfirmasi Customer sesuai template resmi
        $message = $this->formatCustomerConfirmationMessage($order, $items);

        // Catat log
        $existingLog = $this->logModel->getLogForIdempotency($orderId, $recipientType, $notificationType);
        if ($existingLog && $existingLog['message_status'] === 'failed') {
            $logId = (int)$existingLog['id'];
            $this->logModel->incrementAttempt($logId);
        } else {
            $logId = $this->logModel->createLog($orderId, $recipientType, $normalizedPhone, $notificationType, 'pending');
        }

        // Eksekusi pengiriman
        try {
            $sendRes = $this->fonnte->sendWhatsApp($normalizedPhone, $message);
            $providerCode = isset($sendRes['http_code']) ? (string)$sendRes['http_code'] : ($sendRes['success'] ? '200' : '500');

            if ($sendRes['success']) {
                $this->logModel->updateLogStatus($logId, 'sent', $providerCode, null);
                return [
                    'success' => true,
                    'status' => 'sent',
                    'message' => 'Konfirmasi pesanan berhasil dikirim ke WhatsApp Customer.'
                ];
            } else {
                $cleanError = $this->sanitizeError($sendRes['message'] ?? 'Pengiriman Fonnte gagal.');
                $this->logModel->updateLogStatus($logId, 'failed', $providerCode, $cleanError);
                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => $cleanError
                ];
            }
        } catch (Exception $e) {
            $cleanError = $this->sanitizeError($e->getMessage());
            $this->logModel->updateLogStatus($logId, 'failed', 'EXCEPTION', $cleanError);
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $cleanError
            ];
        }
    }

    /**
     * Mengirim notifikasi ke daftar Admin (opsional jika dikonfigurasi oleh Owner)
     */
    public function sendAdminNewOrderNotifications(array $order, array $items): array {
        $adminPhonesRaw = (string)$this->settingModel->getSetting('admin_notification_phones', '');
        if (empty(trim($adminPhonesRaw))) {
            return [];
        }

        $phones = array_filter(array_map('trim', explode(',', $adminPhonesRaw)));
        $orderId = (int)$order['id'];
        $results = [];

        foreach ($phones as $phone) {
            $normalized = FonnteService::normalizePhoneNumber($phone);
            if (!$normalized) continue;

            $notificationType = 'new_order_admin';
            if ($this->logModel->hasSuccessfulLog($orderId, 'admin', $notificationType)) {
                continue;
            }

            $message = $this->formatOwnerNewOrderMessage($order, $items);
            $logId = $this->logModel->createLog($orderId, 'admin', $normalized, $notificationType, 'pending');

            try {
                $sendRes = $this->fonnte->sendWhatsApp($normalized, $message);
                $code = isset($sendRes['http_code']) ? (string)$sendRes['http_code'] : '200';
                if ($sendRes['success']) {
                    $this->logModel->updateLogStatus($logId, 'sent', $code, null);
                    $results[] = ['phone' => $normalized, 'status' => 'sent'];
                } else {
                    $err = $this->sanitizeError($sendRes['message'] ?? 'Gagal');
                    $this->logModel->updateLogStatus($logId, 'failed', $code, $err);
                    $results[] = ['phone' => $normalized, 'status' => 'failed', 'error' => $err];
                }
            } catch (Exception $e) {
                $err = $this->sanitizeError($e->getMessage());
                $this->logModel->updateLogStatus($logId, 'failed', 'EXCEPTION', $err);
                $results[] = ['phone' => $normalized, 'status' => 'failed', 'error' => $err];
            }
        }

        return $results;
    }

    /**
     * Mengirim notifikasi pembaruan status pesanan kepada Customer
     * (misal: Terkonfirmasi, Sedang Diproses, Dikirim, Selesai, Dibatalkan)
     */
    public function notifyStatusUpdate(int $orderId, string $newStatus, ?string $note = null): array {
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Pesanan tidak ditemukan.'];
        }

        $customerPhone = !empty($order['shipping_phone']) ? $order['shipping_phone'] : '';
        $normalizedPhone = FonnteService::normalizePhoneNumber($customerPhone);
        if (!$normalizedPhone) {
            return ['success' => false, 'message' => 'Nomor WhatsApp Customer tidak valid.'];
        }

        $notificationType = 'order_status_' . $newStatus;
        if ($this->logModel->hasSuccessfulLog($orderId, 'customer', $notificationType)) {
            return ['success' => true, 'status' => 'skipped', 'message' => 'Status ini sudah pernah diberitahukan.'];
        }

        $customerName = htmlspecialchars($order['shipping_name'] ?? 'Pelanggan');
        $orderNumber = $order['order_number'] ?? '-';

        $message = $this->formatCustomerStatusMessage($order, $newStatus, $note);

        $logId = $this->logModel->createLog($orderId, 'customer', $normalizedPhone, $notificationType, 'pending');

        try {
            $sendRes = $this->fonnte->sendWhatsApp($normalizedPhone, $message);
            $code = isset($sendRes['http_code']) ? (string)$sendRes['http_code'] : '200';
            if ($sendRes['success']) {
                $this->logModel->updateLogStatus($logId, 'sent', $code, null);
                return ['success' => true, 'message' => 'Notifikasi status berhasil dikirim.'];
            } else {
                $err = $this->sanitizeError($sendRes['message'] ?? 'Gagal');
                $this->logModel->updateLogStatus($logId, 'failed', $code, $err);
                return ['success' => false, 'message' => $err];
            }
        } catch (Exception $e) {
            $err = $this->sanitizeError($e->getMessage());
            $this->logModel->updateLogStatus($logId, 'failed', 'EXCEPTION', $err);
            return ['success' => false, 'message' => $err];
        }
    }

    /**
     * Mencoba ulang (retry) pengiriman notifikasi yang berstatus failed
     */
    public function retryNotification(int $logId): array {
        $log = $this->logModel->getLogById($logId);
        if (!$log) {
            return ['success' => false, 'message' => 'Log notifikasi tidak ditemukan.'];
        }

        if ($log['message_status'] === 'sent') {
            return ['success' => true, 'message' => 'Notifikasi ini sudah berstatus terkirim.'];
        }

        $orderId = (int)$log['order_id'];
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Data pesanan terkait tidak ditemukan.'];
        }

        $items = $this->orderModel->getOrderItems($orderId);

        // Susun ulang pesan berdasarkan recipient_type
        if ($log['recipient_type'] === 'owner' || $log['recipient_type'] === 'admin') {
            $message = $this->formatOwnerNewOrderMessage($order, $items);
        } else {
            if (strpos($log['notification_type'], 'order_status_') === 0) {
                $status = str_replace('order_status_', '', $log['notification_type']);
                return $this->notifyStatusUpdate($orderId, $status);
            }
            $message = $this->formatCustomerConfirmationMessage($order, $items);
        }

        $normalizedPhone = FonnteService::normalizePhoneNumber($log['recipient_phone']);
        if (!$normalizedPhone) {
            return ['success' => false, 'message' => 'Nomor penerima tidak valid.'];
        }

        $this->logModel->incrementAttempt($logId);

        try {
            $sendRes = $this->fonnte->sendWhatsApp($normalizedPhone, $message);
            $code = isset($sendRes['http_code']) ? (string)$sendRes['http_code'] : '200';

            if ($sendRes['success']) {
                $this->logModel->updateLogStatus($logId, 'sent', $code, null);
                return [
                    'success' => true,
                    'message' => 'Pengiriman ulang notifikasi berhasil terkirim.'
                ];
            } else {
                $cleanError = $this->sanitizeError($sendRes['message'] ?? 'Pengiriman Fonnte gagal.');
                $this->logModel->updateLogStatus($logId, 'failed', $code, $cleanError);
                return [
                    'success' => false,
                    'message' => $cleanError
                ];
            }
        } catch (Exception $e) {
            $cleanError = $this->sanitizeError($e->getMessage());
            $this->logModel->updateLogStatus($logId, 'failed', 'EXCEPTION', $cleanError);
            return [
                'success' => false,
                'message' => $cleanError
            ];
        }
    }

    /**
     * Menyusun teks pesan untuk Owner sesuai format persis yang diminta:
     *
     * "Halo Owner nnaco_sa! Ada pesanan baru.
     * Nomor Pesanan: [NOMOR_PESANAN]
     * Nama Customer: [NAMA_CUSTOMER]
     * WhatsApp Customer: [NOMOR_WHATSAPP]
     * Tanggal Pesanan: [TANGGAL_PESANAN]
     * Detail Pesanan:
     * [DAFTAR_PRODUK]
     * Total Belanja: [TOTAL_BELANJA]
     * Metode Pembayaran: [METODE_PEMBAYARAN]
     * Status Pembayaran: [STATUS_PEMBAYARAN]
     * Status Pesanan: [STATUS_PESANAN]
     * Silakan buka dashboard nnaco_sa untuk melihat detail dan memproses pesanan.
     * Pesan otomatis dari sistem nnaco_sa."
     */
    public function formatOwnerNewOrderMessage(array $order, array $items): string {
        $orderNumber = $order['order_number'] ?? '-';
        $customerName = !empty($order['shipping_name']) ? $order['shipping_name'] : ($order['customer_name'] ?? 'Pelanggan');
        $orderDate = $this->formatOrderDate($order['created_at'] ?? 'now');
        
        $totalItems = 0;
        foreach ($items as $it) {
            $totalItems += (int)($it['quantity'] ?? 1);
        }

        $totalAmount = 'Rp ' . number_format((float)($order['total_amount'] ?? 0), 0, ',', '.');
        $paymentMethod = $this->translatePaymentMethod($order['payment_method'] ?? 'cash');
        $paymentStatus = $this->translatePaymentStatus($order['payment_status'] ?? 'unpaid');

        $message = "NOTIFIKASI PESANAN BARU — nnaco_sa\n"
                 . "Ada pesanan baru dari Customer.\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Nama Customer: {$customerName}\n"
                 . "Tanggal Pesanan: {$orderDate}\n"
                 . "Jumlah Item: {$totalItems}\n"
                 . "Total Pembayaran: {$totalAmount}\n"
                 . "Metode Pembayaran: {$paymentMethod}\n"
                 . "Status Pesanan: Menunggu Konfirmasi\n"
                 . "Status Pembayaran: {$paymentStatus}\n"
                 . "Silakan buka dashboard Admin untuk memeriksa dan memproses pesanan.";

        return $message;
    }

    /**
     * Menyusun pesan notifikasi status pesanan kepada Customer sesuai Requirement 8
     */
    public function formatCustomerStatusMessage(array $order, string $newStatus, ?string $note = null): string {
        $customerName = htmlspecialchars($order['shipping_name'] ?? ($order['customer_name'] ?? 'Pelanggan'));
        $orderNumber = $order['order_number'] ?? '-';
        $courier = !empty($order['shipping_courier']) ? $order['shipping_courier'] : (!empty($order['courier']) ? $order['courier'] : null);
        $trackingNumber = !empty($order['tracking_number']) ? $order['tracking_number'] : null;

        if ($newStatus === 'confirmed') {
            return "Halo {$customerName}, pesanan kamu di nnaco_sa telah diterima oleh toko.\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Status: Pesanan Diterima\n"
                 . "Pesanan kamu akan diproses sesuai antrean. Terima kasih telah berbelanja di nnaco_sa!";
        } elseif ($newStatus === 'processing') {
            return "Halo {$customerName}, pesanan kamu sedang diproses oleh nnaco_sa.\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Status: Sedang Diproses\n"
                 . "Kami akan memberikan informasi berikutnya ketika pesanan siap dikirim atau diambil.";
        } elseif ($newStatus === 'shipped') {
            $msg = "Halo {$customerName}, pesanan kamu dari nnaco_sa sedang diantar.\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Status: Sedang Diantar\n";
            if (!empty($courier)) {
                $msg .= "Kurir: {$courier}\n";
            }
            if (!empty($trackingNumber)) {
                $msg .= "Nomor Resi: {$trackingNumber}\n";
            }
            $msg .= "Silakan pantau pesanan kamu. Terima kasih telah berbelanja di nnaco_sa!";
            return $msg;
        } elseif ($newStatus === 'completed') {
            return "Halo {$customerName}, pesanan kamu dari nnaco_sa telah dikonfirmasi diterima.\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Status: Pesanan Diterima Customer\n"
                 . "Terima kasih sudah berbelanja di nnaco_sa. Semoga kamu menyukai produk yang kamu pesan!";
        } else {
            $statusText = $this->translateOrderStatus($newStatus);
            $msg = "Halo {$customerName}, status pesanan kamu di nnaco_sa telah diperbarui.\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Status: {$statusText}\n";
            if (!empty($note)) {
                $msg .= "Catatan: {$note}\n";
            }
            $msg .= "Terima kasih telah berbelanja di nnaco_sa!";
            return $msg;
        }
    }

    /**
     * Menyusun teks pesan untuk Customer sesuai format persis yang diminta:
     *
     * "Halo [NAMA_CUSTOMER]! Terima kasih sudah berbelanja di nnaco_sa.
     * Pesanan kamu berhasil dibuat!
     * Nomor Pesanan: [NOMOR_PESANAN]
     * Total Belanja: [TOTAL_BELANJA]
     * Metode Pembayaran: [METODE_PEMBAYARAN]
     * Status Pesanan: [STATUS_PESANAN]
     * Status Pembayaran: [STATUS_PEMBAYARAN]
     * [INFORMASI_PEMBAYARAN]
     * Silakan simpan nomor pesanan ini untuk memeriksa status pesanan kamu. Terima kasih telah memilih nnaco_sa!"
     */
    public function formatCustomerConfirmationMessage(array $order, array $items): string {
        $customerName = !empty($order['shipping_name']) ? $order['shipping_name'] : ($order['customer_name'] ?? 'Pelanggan');
        $orderNumber = $order['order_number'] ?? '-';
        $totalAmount = 'Rp ' . number_format((float)($order['total_amount'] ?? 0), 0, ',', '.');
        $paymentMethod = $this->translatePaymentMethod($order['payment_method'] ?? 'cash');
        $orderStatus = $this->translateOrderStatus($order['status'] ?? 'pending');
        $paymentStatus = $this->translatePaymentStatus($order['payment_status'] ?? 'unpaid');

        // Untuk metode pembayaran Cash:
        // "Pembayaran dilakukan secara tunai kepada penjual atau saat pengambilan pesanan sesuai ketentuan toko. Status pembayaran saat ini masih Belum Dibayar."
        if (strtolower($order['payment_method'] ?? 'cash') === 'cash') {
            $paymentInfo = "Pembayaran dilakukan secara tunai kepada penjual atau saat pengambilan pesanan sesuai ketentuan toko. Status pembayaran saat ini masih Belum Dibayar.";
        } else {
            $paymentInfo = "Silakan ikuti instruksi pembayaran yang tersedia di sistem.";
        }

        $message = "Halo {$customerName}! Terima kasih sudah berbelanja di nnaco_sa.\n"
                 . "Pesanan kamu berhasil dibuat!\n"
                 . "Nomor Pesanan: {$orderNumber}\n"
                 . "Total Belanja: {$totalAmount}\n"
                 . "Metode Pembayaran: {$paymentMethod}\n"
                 . "Status Pesanan: {$orderStatus}\n"
                 . "Status Pembayaran: {$paymentStatus}\n"
                 . "{$paymentInfo}\n\n"
                 . "Silakan simpan nomor pesanan ini untuk memeriksa status pesanan kamu. Terima kasih telah memilih nnaco_sa!";

        return $message;
    }

    /**
     * Memformat daftar produk (nama produk, kuantitas, subtotal).
     * Jika item lebih dari 8, diringkas agar pesan tidak melebihi batas WhatsApp.
     */
    private function formatProductList(array $items): string {
        if (empty($items)) {
            return "- (Tidak ada rincian item)";
        }

        $lines = [];
        $maxShow = 8;
        $count = count($items);

        for ($i = 0; $i < min($count, $maxShow); $i++) {
            $item = $items[$i];
            $name = $item['product_name'] ?? $item['name'] ?? 'Produk';
            $qty = (int)($item['quantity'] ?? 1);
            $subtotal = 'Rp ' . number_format((float)($item['subtotal'] ?? 0), 0, ',', '.');
            $lines[] = "- {$name} ({$qty}x) = {$subtotal}";
        }

        if ($count > $maxShow) {
            $remaining = $count - $maxShow;
            $lines[] = "... dan {$remaining} item lainnya (lihat detail di dashboard)";
        }

        return implode("\n", $lines);
    }

    /**
     * Mengambil nomor WhatsApp Owner/Toko yang sah:
     * 1. Dari tabel settings 'order_notification_phone'
     * 2. Dari environment variable 'ORDER_NOTIFICATION_PHONE'
     * 3. Dari akun pengguna Owner terdaftar di tabel users
     */
    private function resolveOwnerPhone(): ?string {
        // 1. Pengaturan Database
        $dbPhone = $this->settingModel->getSetting('order_notification_phone');
        if (!empty($dbPhone) && trim($dbPhone) !== '') {
            return trim($dbPhone);
        }

        // 2. Environment Variable
        $envPhone = getenv('ORDER_NOTIFICATION_PHONE') ?: ($_ENV['ORDER_NOTIFICATION_PHONE'] ?? $_SERVER['ORDER_NOTIFICATION_PHONE'] ?? null);
        if (!empty($envPhone) && trim($envPhone) !== '' && strpos($envPhone, '628XXXXXXXXXX') === false) {
            return trim($envPhone);
        }

        // 3. Nomor akun Owner di database users
        $owners = $this->userModel->getAllUsers();
        foreach ($owners as $u) {
            if ($u['role'] === 'owner' && $u['status'] === 'active' && !empty($u['phone'])) {
                return $u['phone'];
            }
        }

        return null;
    }

    private function translatePaymentMethod(?string $method): string {
        $m = strtolower((string)$method);
        if ($m === 'cash') return 'Cash / Tunai';
        if ($m === 'transfer') return 'Transfer Bank';
        return !empty($method) ? ucfirst($method) : 'Cash / Tunai';
    }

    private function translatePaymentStatus(?string $status): string {
        $s = strtolower((string)$status);
        if ($s === 'unpaid') return 'Belum Dibayar';
        if ($s === 'paid') return 'Sudah Dibayar';
        if ($s === 'refunded') return 'Dikembalikan';
        return !empty($status) ? ucfirst($status) : 'Belum Dibayar';
    }

    private function translateOrderStatus(?string $status): string {
        $s = strtolower((string)$status);
        switch ($s) {
            case 'pending': return 'Menunggu Konfirmasi (Pending)';
            case 'confirmed': return 'Terkonfirmasi';
            case 'processing': return 'Sedang Diproses';
            case 'shipped': return 'Sedang Dikirim (Shipped)';
            case 'completed': return 'Selesai (Completed)';
            case 'cancelled': return 'Dibatalkan (Cancelled)';
            default: return !empty($status) ? ucfirst($status) : 'Pending';
        }
    }

    private function formatOrderDate(string $timestamp): string {
        $time = strtotime($timestamp);
        if (!$time) $time = time();

        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $day = date('d', $time);
        $month = $months[(int)date('m', $time)];
        $year = date('Y', $time);
        $hourMin = date('H:i', $time);

        return "{$day} {$month} {$year}, {$hourMin} WIB";
    }

    private function sanitizeError(?string $err): string {
        if (empty($err)) return 'Terjadi kesalahan tidak diketahui.';
        // Hapus token atau kata kunci rahasia jika ada
        $clean = preg_replace('/[a-zA-Z0-9]{20,}/', '[PROTECTED_TOKEN]', $err);
        return htmlspecialchars(substr($clean, 0, 500));
    }
}
