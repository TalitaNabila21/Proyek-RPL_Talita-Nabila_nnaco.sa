<?php

require_once '../app/services/FonnteService.php';
require_once '../app/services/OrderNotificationService.php';
require_once '../app/services/GroqAIProvider.php';
require_once '../app/services/GeminiAIProvider.php';
require_once '../app/services/NvidiaAIProvider.php';

class AdminSettingsController extends Controller {

    public function __construct() {
        // Kontrol Akses: Hanya Admin dan Owner yang berwenang mengelola pengaturan sistem
        $this->requireRole(['admin', 'owner']);
    }

    /**
     * Dashboard Ringkasan Pengaturan Sistem
     */
    public function index() {
        $settingModel = $this->model('Setting');
        $allSettings = $settingModel->getAllSettings();

        // 1. Status AI Model
        $aiEnabled = (string)($allSettings['ai_enabled'] ?? '1') === '1';
        $aiProvider = strtolower((string)($allSettings['ai_provider'] ?? 'groq'));
        $aiModel = $allSettings['ai_model'] ?? 'qwen/qwen3.8-27b';
        $aiHasKey = !empty($allSettings['ai_api_key']) 
                 || !empty($allSettings['groq_api_key']) 
                 || !empty($allSettings['gemini_api_key']) 
                 || !empty($allSettings['nvidia_api_key'])
                 || !empty(getenv('AI_API_KEY'));

        // 2. Status OTP WhatsApp
        $otpEnabled = (string)($allSettings['otp_enabled'] ?? '1') === '1';
        $otpExpiry = (int)($allSettings['otp_expiry_minutes'] ?? 5);
        $otpLength = (int)($allSettings['otp_length'] ?? 6);
        $otpCooldown = (int)($allSettings['otp_resend_cooldown'] ?? 60);

        // 3. Status Fonnte Gateway & Notifikasi
        $fonnte = new FonnteService();
        $isFonnteConfigured = $fonnte->isConfigured();
        $deviceDetails = $fonnte->getDeviceDetails();
        $isDeviceConnected = ($deviceDetails['device_status'] ?? '') === 'connect';

        $data = [
            'judul' => 'Pengaturan Sistem',
            'user_role' => $_SESSION['user_role'] ?? '',
            'ai' => [
                'enabled' => $aiEnabled,
                'provider' => $aiProvider,
                'model' => $aiModel,
                'has_key' => $aiHasKey
            ],
            'otp' => [
                'enabled' => $otpEnabled,
                'expiry' => $otpExpiry,
                'length' => $otpLength,
                'cooldown' => $otpCooldown
            ],
            'whatsapp' => [
                'configured' => $isFonnteConfigured,
                'connected' => $isDeviceConnected,
                'device_name' => $deviceDetails['name'] ?? '-',
                'device_phone' => $deviceDetails['device'] ?? '-',
                'quota' => $deviceDetails['quota'] ?? 0,
                'owner_phone' => $allSettings['order_notification_phone'] ?? ''
            ],
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('layouts/admin', [
            'view' => 'admin_settings/index',
            'data' => $data
        ]);
    }

    /**
     * Halaman Konfigurasi AI Model
     */
    public function ai() {
        $settingModel = $this->model('Setting');
        $all = $settingModel->getAllSettings();

        $provider = strtolower((string)($all['ai_provider'] ?? 'groq'));
        $apiKey = $all['ai_api_key'] ?? ($all[$provider . '_api_key'] ?? (getenv('AI_API_KEY') ?: ''));

        // Masking API key untuk keamanan (hanya 4 karakter awal dan akhir yang terlihat)
        $maskedKey = '';
        if (!empty($apiKey)) {
            $len = strlen($apiKey);
            if ($len > 8) {
                $maskedKey = substr($apiKey, 0, 4) . str_repeat('•', max(4, $len - 8)) . substr($apiKey, -4);
            } else {
                $maskedKey = str_repeat('•', 8);
            }
        }

        $data = [
            'judul' => 'Konfigurasi AI Model',
            'user_role' => $_SESSION['user_role'] ?? '',
            'ai_enabled' => (string)($all['ai_enabled'] ?? '1'),
            'ai_provider' => $provider,
            'ai_model' => $all['ai_model'] ?? 'qwen/qwen3.8-27b',
            'ai_api_key' => $apiKey,
            'ai_api_key_masked' => $maskedKey,
            'groq_api_key' => $all['groq_api_key'] ?? '',
            'gemini_api_key' => $all['gemini_api_key'] ?? '',
            'nvidia_api_key' => $all['nvidia_api_key'] ?? '',
            'ai_temperature' => $all['ai_temperature'] ?? '0.5',
            'ai_max_tokens' => $all['ai_max_tokens'] ?? '500',
            'ai_system_instruction' => $all['ai_system_instruction'] ?? '',
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('layouts/admin', [
            'view' => 'admin_settings/ai',
            'data' => $data
        ]);
    }

    /**
     * Menyimpan Konfigurasi AI Model
     */
    public function saveAi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminSettings/ai");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $aiEnabled = isset($_POST['ai_enabled']) && $_POST['ai_enabled'] === '1' ? '1' : '0';
        $aiProvider = strtolower(trim($_POST['ai_provider'] ?? 'groq'));
        if (!in_array($aiProvider, ['groq', 'gemini', 'nvidia'])) {
            $aiProvider = 'groq';
        }

        $aiModel = trim($_POST['ai_model'] ?? '');
        if (empty($aiModel)) {
            if ($aiProvider === 'gemini') $aiModel = 'gemini-1.5-flash';
            elseif ($aiProvider === 'nvidia') $aiModel = 'nvidia/llama-3.1-nemotron-51b-instruct';
            else $aiModel = 'qwen/qwen3.8-27b';
        }

        $temperature = (float)($_POST['ai_temperature'] ?? 0.5);
        if ($temperature < 0.0) $temperature = 0.0;
        if ($temperature > 1.0) $temperature = 1.0;

        $maxTokens = (int)($_POST['ai_max_tokens'] ?? 500);
        if ($maxTokens < 50) $maxTokens = 50;
        if ($maxTokens > 2048) $maxTokens = 2048;

        $systemInstruction = trim($_POST['ai_system_instruction'] ?? '');

        // API Key logic: Jika input tidak kosong dan bukan string mask (tidak memuat •), update key
        $apiKeyInput = trim($_POST['ai_api_key'] ?? '');
        $settingModel = $this->model('Setting');

        $updates = [
            'ai_enabled' => $aiEnabled,
            'ai_provider' => $aiProvider,
            'ai_model' => $aiModel,
            'ai_temperature' => (string)$temperature,
            'ai_max_tokens' => (string)$maxTokens,
            'ai_system_instruction' => $systemInstruction
        ];

        if (!empty($apiKeyInput) && strpos($apiKeyInput, '•') === false) {
            $updates['ai_api_key'] = $apiKeyInput;
            $updates[$aiProvider . '_api_key'] = $apiKeyInput;
        }

        $success = $settingModel->updateSettings($updates);

        if ($success) {
            $_SESSION['flash_msg'] = 'Konfigurasi AI Model berhasil disimpan dan langsung aktif!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal menyimpan konfigurasi AI Model ke database.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/adminSettings/ai");
        exit;
    }

    /**
     * Endpoint AJAX Uji Coba Koneksi AI Model
     */
    public function testAi() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true) ?: $_POST;

        $csrfToken = $input['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            echo json_encode(['status' => false, 'message' => 'Token keamanan (CSRF) tidak valid. Silakan refresh halaman.']);
            exit;
        }

        $provider = strtolower(trim($input['provider'] ?? 'groq'));
        $model = trim($input['model'] ?? '');
        $temperature = isset($input['temperature']) ? (float)$input['temperature'] : 0.5;
        $maxTokens = isset($input['max_tokens']) ? (int)$input['max_tokens'] : 500;
        $apiKey = trim($input['api_key'] ?? '');
        $instruction = trim($input['system_instruction'] ?? '');

        // Jika API Key dikirim dalam bentuk masked (mengandung •), ambil key asli dari database settings
        if (empty($apiKey) || strpos($apiKey, '•') !== false) {
            $settingModel = $this->model('Setting');
            $apiKey = $settingModel->getSetting($provider . '_api_key') 
                   ?: $settingModel->getSetting('ai_api_key') 
                   ?: getenv('AI_API_KEY');
        }

        if (empty($apiKey)) {
            echo json_encode(['status' => false, 'message' => 'API Key wajib diisi untuk melakukan tes koneksi.']);
            exit;
        }

        $testContext = "=== CONTEXT PENGUJIAN SISTEM NNACO_SA ===\nToko Aksesori nnaco_sa menyediakan produk gelang, kalung, dan cincin handmade cantik.";
        $testPrompt = "Perkenalkan dirimu dalam 1-2 kalimat singkat dan sebutkan namamu sebagai AI Assistant nnaco_sa.";

        $startTime = microtime(true);

        try {
            if ($provider === 'gemini') {
                $service = new GeminiAIProvider($apiKey, $model, $temperature, $maxTokens, $instruction);
            } elseif ($provider === 'nvidia') {
                $service = new NvidiaAIProvider($apiKey, $model, $temperature, $maxTokens, $instruction);
            } else {
                $service = new GroqAIProvider($apiKey, $model, $temperature, $maxTokens, $instruction);
            }

            $response = $service->askQuestion($testContext, $testPrompt);
            $latency = round((microtime(true) - $startTime) * 1000);

            echo json_encode([
                'status' => true,
                'message' => 'Koneksi ke AI Provider berhasil!',
                'provider' => strtoupper($provider),
                'model' => $model ?: ($provider === 'gemini' ? 'gemini-1.5-flash' : 'qwen/qwen3.8-27b'),
                'latency_ms' => $latency,
                'response' => $response
            ]);
        } catch (Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000);
            $cleanError = htmlspecialchars(preg_replace('/[a-zA-Z0-9]{25,}/', '[PROTECTED_KEY]', $e->getMessage()));
            echo json_encode([
                'status' => false,
                'message' => 'Gagal terhubung ke AI: ' . $cleanError,
                'latency_ms' => $latency
            ]);
        }
        exit;
    }

    /**
     * Halaman Konfigurasi Authentication OTP WhatsApp
     */
    public function otp() {
        $settingModel = $this->model('Setting');
        $all = $settingModel->getAllSettings();

        $token = $all['fonnte_token'] ?? (getenv('FONNTE_TOKEN') ?: '');
        $maskedToken = '';
        if (!empty($token)) {
            $len = strlen($token);
            if ($len > 6) {
                $maskedToken = substr($token, 0, 3) . str_repeat('•', max(4, $len - 6)) . substr($token, -3);
            } else {
                $maskedToken = str_repeat('•', 6);
            }
        }

        $fonnte = new FonnteService();
        $deviceDetails = $fonnte->getDeviceDetails();

        $data = [
            'judul' => 'Authentication OTP WhatsApp',
            'user_role' => $_SESSION['user_role'] ?? '',
            'otp_enabled' => (string)($all['otp_enabled'] ?? '1'),
            'fonnte_token' => $token,
            'fonnte_token_masked' => $maskedToken,
            'otp_expiry_minutes' => (int)($all['otp_expiry_minutes'] ?? 5),
            'otp_max_attempts' => (int)($all['otp_max_attempts'] ?? 5),
            'otp_resend_cooldown' => (int)($all['otp_resend_cooldown'] ?? 60),
            'otp_rate_limit_per_hour' => (int)($all['otp_rate_limit_per_hour'] ?? 15),
            'otp_length' => (int)($all['otp_length'] ?? 6),
            'fonnte_status' => [
                'configured' => $fonnte->isConfigured(),
                'connected' => ($deviceDetails['device_status'] ?? '') === 'connect',
                'device' => $deviceDetails['device'] ?? '-',
                'name' => $deviceDetails['name'] ?? '-',
                'quota' => $deviceDetails['quota'] ?? 0
            ],
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('layouts/admin', [
            'view' => 'admin_settings/otp',
            'data' => $data
        ]);
    }

    /**
     * Menyimpan Konfigurasi Authentication OTP WhatsApp
     */
    public function saveOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminSettings/otp");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $otpEnabled = isset($_POST['otp_enabled']) && $_POST['otp_enabled'] === '1' ? '1' : '0';
        $expiryMinutes = max(1, min(30, (int)($_POST['otp_expiry_minutes'] ?? 5)));
        $maxAttempts = max(1, min(10, (int)($_POST['otp_max_attempts'] ?? 5)));
        $cooldown = max(15, min(300, (int)($_POST['otp_resend_cooldown'] ?? 60)));
        $rateLimit = max(3, min(100, (int)($_POST['otp_rate_limit_per_hour'] ?? 15)));
        $otpLength = ((int)($_POST['otp_length'] ?? 6)) === 4 ? 4 : 6;

        $tokenInput = trim($_POST['fonnte_token'] ?? '');

        $updates = [
            'otp_enabled' => $otpEnabled,
            'otp_expiry_minutes' => (string)$expiryMinutes,
            'otp_max_attempts' => (string)$maxAttempts,
            'otp_resend_cooldown' => (string)$cooldown,
            'otp_rate_limit_per_hour' => (string)$rateLimit,
            'otp_length' => (string)$otpLength
        ];

        if (!empty($tokenInput) && strpos($tokenInput, '•') === false) {
            $updates['fonnte_token'] = $tokenInput;
        }

        $settingModel = $this->model('Setting');
        $success = $settingModel->updateSettings($updates);

        if ($success) {
            $_SESSION['flash_msg'] = 'Konfigurasi Authentication WhatsApp OTP berhasil disimpan!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal menyimpan konfigurasi OTP ke database.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/adminSettings/otp");
        exit;
    }

    /**
     * Endpoint AJAX Uji Coba Pengiriman OTP WhatsApp
     */
    public function testOtp() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true) ?: $_POST;

        $csrfToken = $input['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            echo json_encode(['status' => false, 'message' => 'Token CSRF tidak valid. Silakan refresh halaman.']);
            exit;
        }

        $phone = trim($input['phone'] ?? '');
        if (empty($phone)) {
            echo json_encode(['status' => false, 'message' => 'Nomor WhatsApp tujuan tes wajib diisi.']);
            exit;
        }

        $normalized = FonnteService::normalizePhoneNumber($phone);
        if (!$normalized) {
            echo json_encode(['status' => false, 'message' => 'Format nomor WhatsApp tidak valid.']);
            exit;
        }

        $customToken = trim($input['token'] ?? '');
        if (empty($customToken) || strpos($customToken, '•') !== false) {
            $customToken = null;
        }

        $fonnte = new FonnteService($customToken);
        if (!$fonnte->isConfigured()) {
            echo json_encode(['status' => false, 'message' => 'Token Fonnte belum dikonfigurasi.']);
            exit;
        }

        $sampleCode = (string)random_int(100000, 999999);
        $message = "SIMULASI PENGUJIAN OTP nnaco_sa\n\n"
                 . "Kode OTP Simulasi: *{$sampleCode}*\n\n"
                 . "Pesan ini adalah uji coba koneksi gateway Fonnte dari Dashboard Pengaturan Sistem nnaco_sa. Jika Anda menerima pesan ini, konfigurasi WhatsApp OTP telah berfungsi sempurna!";

        $startTime = microtime(true);
        $sendRes = $fonnte->sendWhatsApp($normalized, $message);
        $latency = round((microtime(true) - $startTime) * 1000);

        if (!empty($sendRes['success'])) {
            echo json_encode([
                'status' => true,
                'message' => "Pesan OTP uji coba berhasil dikirim ke {$normalized}!",
                'code_sent' => $sampleCode,
                'latency_ms' => $latency,
                'detail' => $sendRes
            ]);
        } else {
            $cleanError = htmlspecialchars($sendRes['message'] ?? 'Gateway Fonnte menolak pengiriman.');
            echo json_encode([
                'status' => false,
                'message' => "Gagal mengirim OTP: {$cleanError}",
                'latency_ms' => $latency
            ]);
        }
        exit;
    }

    /**
     * Halaman Konfigurasi Pengaturan Notifikasi WhatsApp
     */
    public function whatsapp() {
        $settingModel = $this->model('Setting');
        $all = $settingModel->getAllSettings();

        $token = $all['fonnte_token'] ?? (getenv('FONNTE_TOKEN') ?: '');
        $maskedToken = '';
        if (!empty($token)) {
            $len = strlen($token);
            if ($len > 6) {
                $maskedToken = substr($token, 0, 3) . str_repeat('•', max(4, $len - 6)) . substr($token, -3);
            } else {
                $maskedToken = str_repeat('•', 6);
            }
        }

        $fonnte = new FonnteService();
        $deviceDetails = $fonnte->getDeviceDetails();

        $data = [
            'judul' => 'Pengaturan Notifikasi WhatsApp',
            'user_role' => $_SESSION['user_role'] ?? '',
            'fonnte_token' => $token,
            'fonnte_token_masked' => $maskedToken,
            'order_notification_phone' => $all['order_notification_phone'] ?? (getenv('ORDER_NOTIFICATION_PHONE') ?: ''),
            'order_notify_owner' => (string)($all['order_notify_owner'] ?? '1'),
            'order_notify_customer' => (string)($all['order_notify_customer'] ?? '1'),
            'order_notify_admin' => (string)($all['order_notify_admin'] ?? '0'),
            'admin_notification_phones' => $all['admin_notification_phones'] ?? '',
            'fonnte_status' => [
                'configured' => $fonnte->isConfigured(),
                'connected' => ($deviceDetails['device_status'] ?? '') === 'connect',
                'device' => $deviceDetails['device'] ?? '-',
                'name' => $deviceDetails['name'] ?? '-',
                'quota' => $deviceDetails['quota'] ?? 0,
                'expired' => $deviceDetails['expired'] ?? '-'
            ],
            'csrf_token' => $this->generateCsrfToken()
        ];

        $this->view('layouts/admin', [
            'view' => 'admin_settings/whatsapp',
            'data' => $data
        ]);
    }

    /**
     * Menyimpan Pengaturan Notifikasi WhatsApp
     */
    public function saveWhatsapp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminSettings/whatsapp");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $orderPhone = trim($_POST['order_notification_phone'] ?? '');
        $notifyOwner = isset($_POST['order_notify_owner']) && $_POST['order_notify_owner'] === '1' ? '1' : '0';
        $notifyCustomer = isset($_POST['order_notify_customer']) && $_POST['order_notify_customer'] === '1' ? '1' : '0';
        $notifyAdmin = isset($_POST['order_notify_admin']) && $_POST['order_notify_admin'] === '1' ? '1' : '0';
        $adminPhones = trim($_POST['admin_notification_phones'] ?? '');
        $tokenInput = trim($_POST['fonnte_token'] ?? '');

        // Normalisasi nomor Owner jika diisi
        if (!empty($orderPhone)) {
            $normalized = FonnteService::normalizePhoneNumber($orderPhone);
            if (!$normalized) {
                $_SESSION['flash_msg'] = 'Format nomor WhatsApp Owner tidak valid. Gunakan format seperti 081234567890.';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/adminSettings/whatsapp");
                exit;
            }
            $orderPhone = $normalized;
        }

        $updates = [
            'order_notification_phone' => $orderPhone,
            'order_notify_owner' => $notifyOwner,
            'order_notify_customer' => $notifyCustomer,
            'order_notify_admin' => $notifyAdmin,
            'admin_notification_phones' => $adminPhones
        ];

        if (!empty($tokenInput) && strpos($tokenInput, '•') === false) {
            $updates['fonnte_token'] = $tokenInput;
        }

        $settingModel = $this->model('Setting');
        $success = $settingModel->updateSettings($updates);

        if ($success) {
            $_SESSION['flash_msg'] = 'Pengaturan Notifikasi WhatsApp berhasil disimpan!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal menyimpan pengaturan notifikasi ke database.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/adminSettings/whatsapp");
        exit;
    }

    /**
     * Endpoint AJAX Uji Coba Pengiriman Pesan WhatsApp Notifikasi
     */
    public function testWhatsapp() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true) ?: $_POST;

        $csrfToken = $input['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            echo json_encode(['status' => false, 'message' => 'Token CSRF tidak valid. Silakan refresh halaman.']);
            exit;
        }

        $phone = trim($input['phone'] ?? '');
        if (empty($phone)) {
            echo json_encode(['status' => false, 'message' => 'Nomor WhatsApp tujuan wajib diisi.']);
            exit;
        }

        $normalized = FonnteService::normalizePhoneNumber($phone);
        if (!$normalized) {
            echo json_encode(['status' => false, 'message' => 'Format nomor WhatsApp tujuan tidak valid.']);
            exit;
        }

        $customToken = trim($input['token'] ?? '');
        if (empty($customToken) || strpos($customToken, '•') !== false) {
            $customToken = null;
        }

        $fonnte = new FonnteService($customToken);
        if (!$fonnte->isConfigured()) {
            echo json_encode(['status' => false, 'message' => 'Token Fonnte belum dikonfigurasi.']);
            exit;
        }

        $testMsg = "TEST NOTIFIKASI WHATSAPP — nnaco_sa\n\n"
                 . "Halo! Ini adalah pesan uji coba dari sistem notifikasi toko nnaco_sa.\n"
                 . "Waktu: " . date('d F Y, H:i:s') . " WIB\n"
                 . "Gateway: Fonnte API\n\n"
                 . "Jika pesan ini sampai di perangkat Anda, sistem notifikasi pesanan dan OTP siap melayani pelanggan dengan sempurna! ✨";

        $startTime = microtime(true);
        $res = $fonnte->sendWhatsApp($normalized, $testMsg);
        $latency = round((microtime(true) - $startTime) * 1000);

        if (!empty($res['success'])) {
            echo json_encode([
                'status' => true,
                'message' => "Pesan uji coba WhatsApp berhasil terkirim ke {$normalized}!",
                'latency_ms' => $latency,
                'detail' => $res
            ]);
        } else {
            $cleanErr = htmlspecialchars($res['message'] ?? 'Gateway menolak pengiriman.');
            echo json_encode([
                'status' => false,
                'message' => "Gagal mengirim pesan: {$cleanErr}",
                'latency_ms' => $latency
            ]);
        }
        exit;
    }

    /**
     * Endpoint AJAX untuk memuat QR Code Fonnte terkini
     */
    public function fonnteQr() {
        header('Content-Type: application/json');
        $fonnte = new FonnteService();
        $qrData = $fonnte->getQrCode();
        $details = $fonnte->getDeviceDetails();

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
     * Endpoint AJAX untuk mengecek status perangkat Fonnte secara real-time
     */
    public function checkStatus() {
        header('Content-Type: application/json');
        $fonnte = new FonnteService();
        $details = $fonnte->getDeviceDetails();
        $isConnected = ($details['device_status'] ?? '') === 'connect';

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
     * Memutuskan pairing perangkat Fonnte
     */
    public function disconnectDevice() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/adminSettings/whatsapp");
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

        header("Location: " . BASEURL . "/adminSettings/whatsapp");
        exit;
    }
}
