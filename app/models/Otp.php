<?php

class Otp {
    private $db;
    private $settingModel;

    public function __construct() {
        $this->db = new Database;
        if (!class_exists('Setting') && file_exists(__DIR__ . '/Setting.php')) {
            require_once __DIR__ . '/Setting.php';
        }
        if (class_exists('Setting')) {
            $this->settingModel = new Setting;
        }
    }

    /**
     * Memeriksa jeda kirim ulang (cooldown) untuk nomor telepon berdasarkan setting
     * @return int Sisa detik jeda tunggu (0 jika sudah boleh kirim ulang)
     */
    public function getResendCooldown(string $phoneNumber, string $purpose = 'login'): int {
        $cooldownLimit = $this->settingModel ? (int)$this->settingModel->getSetting('otp_resend_cooldown', 60) : 60;
        if ($cooldownLimit < 10) $cooldownLimit = 60;

        $this->db->query("SELECT created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_passed 
                          FROM login_otps 
                          WHERE phone_number = :phone AND purpose = :purpose 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('phone', $phoneNumber);
        $this->db->bind('purpose', $purpose);
        $lastOtp = $this->db->single();

        if ($lastOtp) {
            $secondsPassed = (int)$lastOtp['seconds_passed'];
            if ($secondsPassed < $cooldownLimit) {
                return $cooldownLimit - $secondsPassed;
            }
        }

        return 0;
    }

    /**
     * Memeriksa batas frekuensi permintaan OTP (Rate Limit):
     * Toleran untuk localhost/development agar pengujian lancar
     */
    public function isRateLimited(string $phoneNumber, ?string $ipAddress = null): bool {
        $isLocal = empty($ipAddress) || in_array($ipAddress, ['127.0.0.1', '::1', 'localhost']);
        $configuredRateLimit = $this->settingModel ? (int)$this->settingModel->getSetting('otp_rate_limit_per_hour', 15) : 15;
        if ($configuredRateLimit < 3) $configuredRateLimit = 15;

        $maxPhone = $isLocal ? 100 : $configuredRateLimit;
        $maxIp = $isLocal ? 150 : ($configuredRateLimit * 2);

        // Cek per nomor telepon dalam 1 jam terakhir
        $this->db->query("SELECT COUNT(*) as total 
                          FROM login_otps 
                          WHERE phone_number = :phone 
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $this->db->bind('phone', $phoneNumber);
        $phoneCount = (int)($this->db->single()['total'] ?? 0);

        if ($phoneCount >= $maxPhone) {
            return true;
        }

        // Cek per IP address dalam 1 jam terakhir
        if (!empty($ipAddress)) {
            $this->db->query("SELECT COUNT(*) as total 
                              FROM login_otps 
                              WHERE ip_address = :ip 
                              AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $this->db->bind('ip', $ipAddress);
            $ipCount = (int)($this->db->single()['total'] ?? 0);

            if ($ipCount >= $maxIp) {
                return true;
            }
        }

        return false;
    }

    /**
     * Membuat kode OTP acak, membatalkan OTP lama yang belum terpakai,
     * menyimpan hash OTP dan waktu kedaluwarsa dinamis ke database.
     *
     * @return array ['otp_code' => string, 'expires_in_seconds' => int]
     */
    public function createOtp(string $phoneNumber, ?int $userId = null, string $purpose = 'login', ?string $ipAddress = null): array {
        // 1. Batalkan (invalidate) semua OTP lama yang belum digunakan untuk nomor dan tujuan ini
        $this->db->query("UPDATE login_otps 
                          SET used_at = NOW() 
                          WHERE phone_number = :phone 
                          AND purpose = :purpose 
                          AND used_at IS NULL");
        $this->db->bind('phone', $phoneNumber);
        $this->db->bind('purpose', $purpose);
        $this->db->execute();

        // 2. Baca konfigurasi OTP dari database settings
        $expiryMinutes = $this->settingModel ? (int)$this->settingModel->getSetting('otp_expiry_minutes', 5) : 5;
        if ($expiryMinutes < 1 || $expiryMinutes > 60) $expiryMinutes = 5;

        $maxAttempts = $this->settingModel ? (int)$this->settingModel->getSetting('otp_max_attempts', 5) : 5;
        if ($maxAttempts < 1 || $maxAttempts > 20) $maxAttempts = 5;

        $otpLength = $this->settingModel ? (int)$this->settingModel->getSetting('otp_length', 6) : 6;
        if ($otpLength === 4) {
            $otpCode = (string)random_int(1000, 9999);
        } else {
            $otpCode = (string)random_int(100000, 999999);
        }
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);

        // 3. Simpan ke database dengan masa berlaku dinamis (default 5 menit)
        $this->db->query("INSERT INTO login_otps 
                          (user_id, phone_number, otp_hash, purpose, expires_at, attempt_count, max_attempts, ip_address, created_at) 
                          VALUES 
                          (:user_id, :phone, :hash, :purpose, DATE_ADD(NOW(), INTERVAL :expiry MINUTE), 0, :max_attempts, :ip, NOW())");
        $this->db->bind('user_id', $userId);
        $this->db->bind('phone', $phoneNumber);
        $this->db->bind('hash', $otpHash);
        $this->db->bind('purpose', $purpose);
        $this->db->bind('expiry', $expiryMinutes);
        $this->db->bind('max_attempts', $maxAttempts);
        $this->db->bind('ip', $ipAddress);
        $this->db->execute();

        return [
            'otp_code' => $otpCode,
            'expires_in_seconds' => $expiryMinutes * 60
        ];
    }

    /**
     * Membatalkan OTP aktif terbaru jika pengiriman gagal
     */
    public function invalidateLatestOtp(string $phoneNumber, string $purpose = 'login'): void {
        $this->db->query("UPDATE login_otps 
                          SET used_at = NOW() 
                          WHERE phone_number = :phone 
                          AND purpose = :purpose 
                          AND used_at IS NULL");
        $this->db->bind('phone', $phoneNumber);
        $this->db->bind('purpose', $purpose);
        $this->db->execute();
    }

    /**
     * Memverifikasi kode OTP secara atomik
     *
     * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
     */
    public function verifyOtp(string $phoneNumber, string $inputOtp, string $purpose = 'login'): array {
        // Ambil OTP aktif terakhir
        $this->db->query("SELECT * FROM login_otps 
                          WHERE phone_number = :phone 
                          AND purpose = :purpose 
                          AND used_at IS NULL 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('phone', $phoneNumber);
        $this->db->bind('purpose', $purpose);
        $record = $this->db->single();

        if (!$record) {
            return [
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan atau sudah tidak berlaku. Silakan minta kode baru.'
            ];
        }

        // Cek kedaluwarsa waktu (5 menit)
        $expiresAt = strtotime($record['expires_at']);
        if (time() > $expiresAt) {
            // Tandai sudah kedaluwarsa
            $this->db->query("UPDATE login_otps SET used_at = NOW() WHERE id = :id");
            $this->db->bind('id', $record['id']);
            $this->db->execute();

            return [
                'success' => false,
                'message' => 'Kode OTP telah kedaluwarsa (masa berlaku 5 menit). Silakan klik "Kirim Ulang OTP".'
            ];
        }

        // Cek batas percobaan verifikasi (maksimal 5 kali)
        if ((int)$record['attempt_count'] >= (int)$record['max_attempts']) {
            $this->db->query("UPDATE login_otps SET used_at = NOW() WHERE id = :id");
            $this->db->bind('id', $record['id']);
            $this->db->execute();

            return [
                'success' => false,
                'message' => 'Batas percobaan verifikasi telah terlampaui (maksimal 5 kali). Silakan minta kode OTP baru.'
            ];
        }

        // Tambah hitungan percobaan
        $newAttempt = (int)$record['attempt_count'] + 1;
        $this->db->query("UPDATE login_otps SET attempt_count = :count WHERE id = :id");
        $this->db->bind('count', $newAttempt);
        $this->db->bind('id', $record['id']);
        $this->db->execute();

        // Verifikasi kecocokan hash OTP
        if (password_verify($inputOtp, $record['otp_hash'])) {
            // Tandai OTP telah digunakan secara atomik sehingga tidak dapat digunakan kembali
            $this->db->query("UPDATE login_otps SET used_at = NOW() WHERE id = :id");
            $this->db->bind('id', $record['id']);
            $this->db->execute();

            return [
                'success' => true,
                'message' => 'Verifikasi OTP berhasil.',
                'user_id' => $record['user_id'] ? (int)$record['user_id'] : null
            ];
        }

        $remaining = (int)$record['max_attempts'] - $newAttempt;
        if ($remaining <= 0) {
            // Batalkan OTP karena sudah habis kesempatan
            $this->db->query("UPDATE login_otps SET used_at = NOW() WHERE id = :id");
            $this->db->bind('id', $record['id']);
            $this->db->execute();

            return [
                'success' => false,
                'message' => 'Kode OTP yang Anda masukkan salah. Kesempatan habis, silakan minta kode baru.'
            ];
        }

        return [
            'success' => false,
            'message' => "Kode OTP salah. Sisa kesempatan verifikasi: {$remaining} kali lagi."
        ];
    }
}
