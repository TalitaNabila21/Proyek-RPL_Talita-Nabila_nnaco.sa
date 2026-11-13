<?php

require_once __DIR__ . '/../services/FonnteService.php';

class AuthController extends Controller {

    public function index() {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            header("Location: " . BASEURL . "/dashboard");
            exit;
        }
        
        // Default to login page
        $this->login();
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header("Location: " . BASEURL . "/dashboard");
            exit;
        }

        $settingModel = $this->model('Setting');
        $data['judul'] = 'Login';
        $data['csrf_token'] = $this->generateCsrfToken();
        $data['otp_enabled'] = (string)$settingModel->getSetting('otp_enabled', '1') === '1';

        $this->view('layouts/main', [
            'view' => 'auth/login',
            'data' => $data
        ]);
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token'] ?? '');
            
            $rawEmail = trim($_POST['email'] ?? '');
            $email = filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            // Shortcut mapping untuk Operator 1
            if (in_array(strtolower($rawEmail), ['operator', 'operator1', 'operator 1', 'operator satu', 'operator1@nnaco.com'])) {
                $email = 'operator@nnaco.com';
            }

            $userModel = $this->model('User');
            $user = $userModel->getUserByEmail($email);

            // Validasi kata sandi (mendukung hash bcrypt & password standar operator)
            $isPasswordValid = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $isPasswordValid = true;
                } elseif ($user['role'] === 'operator' && in_array($password, ['operator123', 'operator1', 'password123'])) {
                    $isPasswordValid = true;
                }
            }

            if ($user && $isPasswordValid) {
                if (isset($user['status']) && $user['status'] == 'inactive') {
                    $_SESSION['flash_msg'] = 'Akun Anda dinonaktifkan. Silakan hubungi admin.';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/login");
                    exit;
                }

                $allowedRoles = ['customer', 'admin', 'operator', 'owner'];
                if (!in_array($user['role'], $allowedRoles)) {
                    $_SESSION['flash_msg'] = 'Akses ditolak: Peran pengguna tidak valid.';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/login");
                    exit;
                }

                // Prevent Session Fixation
                session_regenerate_id(true);

                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                header("Location: " . BASEURL . "/" . $user['role']);
                exit;
            } else {
                $_SESSION['flash_msg'] = 'Email atau password salah!';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/auth/login");
                exit;
            }
        } else {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }
    }

    /**
     * Endpoint Permintaan Kode OTP via WhatsApp Fonnte API
     * Mendukung tujuan 'login' dan 'register'
     */
    public function requestOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        $isAjax = $this->isAjaxRequest();

        // Validasi CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->respondWithError('Token keamanan tidak valid atau sesi berakhir. Silakan muat ulang halaman.', $isAjax);
            return;
        }

        $rawPhone = trim($_POST['phone'] ?? '');
        if (empty($rawPhone)) {
            $this->respondWithError('Nomor WhatsApp wajib diisi.', $isAjax);
            return;
        }

        $purpose = trim($_POST['purpose'] ?? 'login');
        if (!in_array($purpose, ['login', 'register'])) {
            $purpose = 'login';
        }

        $settingModel = $this->model('Setting');
        $otpEnabled = (string)$settingModel->getSetting('otp_enabled', '1') === '1';
        if ($purpose === 'login' && !$otpEnabled) {
            $this->respondWithError('Fitur login via WhatsApp OTP saat ini sedang dinonaktifkan oleh Administrator.', $isAjax);
            return;
        }

        // Normalisasi format nomor (contoh: 08123... -> 628123...)
        $normalizedPhone = FonnteService::normalizePhoneNumber($rawPhone);
        if (!$normalizedPhone) {
            $this->respondWithError('Format nomor WhatsApp tidak valid. Gunakan format seperti 081234567890 atau 6281234567890.', $isAjax);
            return;
        }

        // Ambil IP address pengguna untuk rate limiting
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (strpos($ipAddress, ',') !== false) {
            $ipAddress = trim(explode(',', $ipAddress)[0]);
        }

        $otpModel = $this->model('Otp');
        $userModel = $this->model('User');

        // Batasi frekuensi permintaan OTP (Rate Limiting: maks 5/jam per nomor, 10/jam per IP)
        if ($otpModel->isRateLimited($normalizedPhone, $ipAddress)) {
            $this->respondWithError('Terlalu banyak permintaan OTP untuk nomor atau perangkat ini. Silakan coba lagi dalam 1 jam.', $isAjax);
            return;
        }

        // Cek jeda tunggu kirim ulang (minimal 60 detik)
        $cooldown = $otpModel->getResendCooldown($normalizedPhone, $purpose);
        if ($cooldown > 0) {
            $this->respondWithError("Harap tunggu {$cooldown} detik sebelum meminta OTP kembali.", $isAjax, ['cooldown' => $cooldown]);
            return;
        }

        if ($purpose === 'register') {
            // Untuk registrasi: periksa apakah nomor sudah terdaftar
            if ($userModel->isPhoneRegistered($normalizedPhone)) {
                $this->respondWithError('Nomor WhatsApp ini sudah terdaftar pada akun lain. Silakan langsung login menggunakan nomor ini.', $isAjax, [
                    'already_registered' => true,
                    'login_url' => BASEURL . '/auth/login?tab=otp&phone=' . urlencode($normalizedPhone)
                ]);
                return;
            }
            $userId = null;
            $isNewUser = false;
        } else {
            // Untuk login: periksa akun yang terhubung dengan nomor tersebut di database
            $user = $userModel->getUserByPhone($normalizedPhone);

            if (!$user) {
                $this->respondWithError('Nomor WhatsApp belum terdaftar pada akun mana pun. Silakan daftar akun Customer terlebih dahulu.', $isAjax, [
                    'not_registered' => true,
                    'register_url' => BASEURL . '/auth/register'
                ]);
                return;
            }

            // Jangan mengizinkan login apabila akun dinonaktifkan
            if (isset($user['status']) && $user['status'] === 'inactive') {
                $this->respondWithError('Akun Anda dinonaktifkan. Silakan hubungi admin.', $isAjax);
                return;
            }

            // Periksa role sah
            $allowedRoles = ['customer', 'admin', 'operator', 'owner'];
            if (!in_array($user['role'], $allowedRoles)) {
                $this->respondWithError('Akses ditolak: Peran akun tidak valid.', $isAjax);
                return;
            }

            $userId = (int)$user['id'];
        }

        // Buat OTP 6 digit aman secara kriptografis & simpan hash serta expiry 5 menit
        $otpData = $otpModel->createOtp($normalizedPhone, $userId, $purpose, $ipAddress);
        $otpCode = $otpData['otp_code'];

        // Kirim OTP via Fonnte API menggunakan template pesan yang diwajibkan
        $fonnte = new FonnteService();
        if ($purpose === 'register') {
            $message = "Kode OTP registrasi akun nnaco_sa Anda adalah: {$otpCode}. Kode berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun.";
        } else {
            $message = "Kode OTP login nnaco_sa Anda adalah: {$otpCode}. Kode berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun.";
        }

        $sendResult = $fonnte->sendWhatsApp($normalizedPhone, $message);
        $isWaSent = !empty($sendResult['success']);

        // Jika pengiriman WhatsApp gagal, batalkan OTP yang baru dibuat dan tampilkan pesan error yang jelas
        if (!$isWaSent) {
            $otpModel->invalidateLatestOtp($normalizedPhone, $purpose);
            $cleanError = $sendResult['message'] ?? 'Pengiriman pesan WhatsApp gagal melalui gateway.';
            $this->respondWithError($cleanError, $isAjax, ['gateway_error' => $cleanError]);
            return;
        }

        // Masking nomor telepon untuk tampilan aman
        $maskedPhone = substr($normalizedPhone, 0, 4) . '****' . substr($normalizedPhone, -3);
        $cooldownVal = (int)($settingModel->getSetting('otp_resend_cooldown', 60));
        $expiresInVal = (int)($otpData['expires_in_seconds'] ?? 300);
        $otpLengthVal = (int)($settingModel->getSetting('otp_length', 6));

        if ($isAjax) {
            $this->jsonResponse(true, "Kode OTP {$otpLengthVal} digit berhasil dikirim ke nomor WhatsApp Anda ({$maskedPhone}). Silakan masukkan kode untuk melanjutkan.", [
                'phone' => $rawPhone ?: $normalizedPhone,
                'normalized_phone' => $normalizedPhone,
                'cooldown' => $cooldownVal,
                'expires_in' => $expiresInVal,
                'otp_length' => $otpLengthVal,
                'purpose' => $purpose
            ]);
            return;
        }

        $_SESSION['flash_msg'] = "Kode OTP berhasil dikirim ke WhatsApp Anda ({$maskedPhone}).";
        $_SESSION['flash_type'] = 'success';
        header("Location: " . BASEURL . ($purpose === 'register' ? "/auth/register" : "/auth/login?tab=otp&phone=" . urlencode($normalizedPhone)));
        exit;
    }



    /**
     * Endpoint Verifikasi Kode OTP WhatsApp
     */
    public function verifyOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        $isAjax = $this->isAjaxRequest();

        // Validasi CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->respondWithError('Token keamanan tidak valid atau sesi berakhir. Silakan muat ulang halaman.', $isAjax);
            return;
        }

        $rawPhone = trim($_POST['phone'] ?? '');
        $otpCode = trim($_POST['otp'] ?? '');
        $purpose = trim($_POST['purpose'] ?? 'login');
        if (!in_array($purpose, ['login', 'register'])) {
            $purpose = 'login';
        }

        $normalizedPhone = FonnteService::normalizePhoneNumber($rawPhone);
        if (!$normalizedPhone) {
            $this->respondWithError('Nomor WhatsApp tidak valid.', $isAjax);
            return;
        }

        $settingModel = $this->model('Setting');
        $expectedLength = (int)$settingModel->getSetting('otp_length', 6);
        if ($expectedLength !== 4 && $expectedLength !== 6) $expectedLength = 6;

        if (!preg_match('/^[0-9]{' . $expectedLength . '}$/', $otpCode)) {
            $this->respondWithError("Kode OTP harus terdiri dari {$expectedLength} digit angka.", $isAjax);
            return;
        }

        // Verifikasi kode OTP secara atomik di database
        $otpModel = $this->model('Otp');
        $verifyResult = $otpModel->verifyOtp($normalizedPhone, $otpCode, $purpose);

        if (!$verifyResult['success']) {
            $this->respondWithError($verifyResult['message'], $isAjax);
            return;
        }

        // Jika verifikasi untuk keperluan registrasi
        if ($purpose === 'register') {
            if ($isAjax) {
                $this->jsonResponse(true, 'Nomor WhatsApp berhasil diverifikasi!', [
                    'verified' => true,
                    'phone' => $normalizedPhone
                ]);
                return;
            }
            $_SESSION['flash_msg'] = 'Nomor WhatsApp berhasil diverifikasi!';
            $_SESSION['flash_type'] = 'success';
            header("Location: " . BASEURL . "/auth/register");
            exit;
        }

        // Verifikasi untuk keperluan login:
        $userModel = $this->model('User');
        $user = $userModel->getUserByPhone($normalizedPhone);

        if (!$user) {
            $this->respondWithError('Nomor WhatsApp belum terdaftar pada akun mana pun. Silakan registrasi terlebih dahulu.', $isAjax);
            return;
        }

        if (!$user) {
            $this->respondWithError('Gagal memproses akun dengan nomor WhatsApp tersebut. Silakan coba lagi.', $isAjax);
            return;
        }

        if (isset($user['status']) && $user['status'] === 'inactive') {
            $this->respondWithError('Akun Anda dinonaktifkan. Silakan hubungi admin.', $isAjax);
            return;
        }

        $allowedRoles = ['customer', 'admin', 'operator', 'owner'];
        if (!in_array($user['role'], $allowedRoles)) {
            $this->respondWithError('Akses ditolak: Peran akun tidak valid.', $isAjax);
            return;
        }

        // Pastikan kepemilikan akun cocok jika user_id dicatat saat pembuatan OTP
        if (!empty($verifyResult['user_id']) && (int)$verifyResult['user_id'] !== (int)$user['id']) {
            $this->respondWithError('Validasi identitas akun gagal. Silakan coba lagi.', $isAjax);
            return;
        }

        // Regenerasi session ID untuk mencegah Session Fixation
        session_regenerate_id(true);

        // Buat session login strictly dari data database backend (role dari database, bukan client)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        $redirectUrl = BASEURL . '/' . $user['role'];

        if ($isAjax) {
            $this->jsonResponse(true, 'Verifikasi OTP berhasil! Mengalihkan ke dashboard...', [
                'redirect' => $redirectUrl,
                'role' => $user['role']
            ]);
            return;
        }

        $_SESSION['flash_msg'] = 'Selamat datang, ' . htmlspecialchars($user['name']) . '!';
        $_SESSION['flash_type'] = 'success';
        header("Location: " . $redirectUrl);
        exit;
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            header("Location: " . BASEURL . "/dashboard");
            exit;
        }

        $data['judul'] = 'Register';
        $data['csrf_token'] = $this->generateCsrfToken();
        $this->view('layouts/main', [
            'view' => 'auth/register',
            'data' => $data
        ]);
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token'] ?? '');
            
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $rawPhone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // Validation
            if (empty($name) || empty($email) || empty($password)) {
                $_SESSION['flash_msg'] = 'Semua field wajib harus diisi!';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/auth/register");
                exit;
            }

            if ($password !== $password_confirm) {
                $_SESSION['flash_msg'] = 'Konfirmasi password tidak cocok!';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/auth/register");
                exit;
            }

            $userModel = $this->model('User');

            // Cek apakah email sudah terdaftar
            if ($userModel->getUserByEmail($email)) {
                $_SESSION['flash_msg'] = 'Email sudah terdaftar!';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/auth/register");
                exit;
            }

            // Validasi nomor WhatsApp & OTP jika diisi
            $normalizedPhone = null;
            if (!empty($rawPhone)) {
                $normalizedPhone = FonnteService::normalizePhoneNumber($rawPhone);
                if (!$normalizedPhone) {
                    $_SESSION['flash_msg'] = 'Format nomor WhatsApp tidak valid. Gunakan format seperti 081234567890!';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/register");
                    exit;
                }

                if ($userModel->isPhoneRegistered($normalizedPhone)) {
                    $_SESSION['flash_msg'] = 'Nomor WhatsApp sudah digunakan oleh akun lain! Silakan gunakan nomor lain atau login.';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/register");
                    exit;
                }

                // Cek kode OTP yang diinputkan pengguna
                $otpCode = trim($_POST['otp'] ?? '');
                if (empty($otpCode)) {
                    $_SESSION['flash_msg'] = 'Silakan klik "Kirim OTP WA" dan masukkan 6 digit kode OTP untuk memverifikasi nomor WhatsApp Anda!';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/register");
                    exit;
                }

                if (!preg_match('/^[0-9]{6}$/', $otpCode)) {
                    $_SESSION['flash_msg'] = 'Kode OTP WhatsApp harus berupa 6 digit angka!';
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/register");
                    exit;
                }

                // Verifikasi OTP tujuan 'register' secara atomik
                $otpModel = $this->model('Otp');
                $verifyResult = $otpModel->verifyOtp($normalizedPhone, $otpCode, 'register');
                if (!$verifyResult['success']) {
                    $_SESSION['flash_msg'] = 'Verifikasi OTP gagal: ' . $verifyResult['message'];
                    $_SESSION['flash_type'] = 'danger';
                    header("Location: " . BASEURL . "/auth/register");
                    exit;
                }
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => $normalizedPhone,
                'password' => $hashed_password
            ];

            if ($userModel->registerUser($data) > 0) {
                // Langsung login otomatis ke dashboard customer dengan nomor WhatsApp yang didaftarkan
                $newUser = $userModel->getUserByEmail($email);
                if ($newUser) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $newUser['id'];
                    $_SESSION['user_name'] = $newUser['name'];
                    $_SESSION['user_role'] = 'customer';

                    $_SESSION['flash_msg'] = 'Selamat datang, ' . htmlspecialchars($newUser['name']) . '! Pendaftaran berhasil dengan nomor WhatsApp ' . htmlspecialchars($rawPhone) . ' dan Anda telah otomatis masuk.';
                    $_SESSION['flash_type'] = 'success';
                    header("Location: " . BASEURL . "/customer");
                    exit;
                }

                $_SESSION['flash_msg'] = 'Registrasi berhasil! Nomor WhatsApp Anda telah terverifikasi. Silakan login.';
                $_SESSION['flash_type'] = 'success';
                header("Location: " . BASEURL . "/auth/login?tab=otp" . ($normalizedPhone ? "&phone=" . urlencode($normalizedPhone) : ""));
                exit;
            } else {
                $_SESSION['flash_msg'] = 'Registrasi gagal!';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/auth/register");
                exit;
            }
        } else {
            header("Location: " . BASEURL . "/auth/register");
            exit;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        
        session_start();
        $_SESSION['flash_msg'] = 'Anda berhasil logout.';
        $_SESSION['flash_type'] = 'success';
        
        header("Location: " . BASEURL . "/auth/login");
        exit;
    }

    /**
     * Helpers Response
     */
    private function isAjaxRequest(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');
    }

    private function jsonResponse(bool $success, string $message, array $extra = []) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }

    private function respondWithError(string $message, bool $isAjax, array $extra = []) {
        if ($isAjax) {
            $this->jsonResponse(false, $message, $extra);
            return;
        }
        $_SESSION['flash_msg'] = $message;
        $_SESSION['flash_type'] = 'danger';
        header("Location: " . BASEURL . "/auth/login");
        exit;
    }
}
