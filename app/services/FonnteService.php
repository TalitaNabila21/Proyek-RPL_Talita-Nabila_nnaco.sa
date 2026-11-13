<?php

/**
 * Service Pengiriman Pesan WhatsApp via Fonnte API
 * Dokumentasi Resmi: https://docs.fonnte.com/
 */
class FonnteService {

    private $token;
    private $apiUrl = 'https://api.fonnte.com/send';
    private $validateUrl = 'https://api.fonnte.com/validate';
    private $deviceUrl = 'https://api.fonnte.com/device';

    public function __construct(?string $token = null) {
        $rawToken = $token;

        // 1. Cek dari database settings jika parameter kosong
        if (empty($rawToken)) {
            try {
                if (!class_exists('Setting') && file_exists(dirname(__DIR__) . '/models/Setting.php')) {
                    require_once dirname(__DIR__) . '/models/Setting.php';
                }
                if (class_exists('Setting')) {
                    $settingModel = new Setting();
                    $dbToken = $settingModel->getSetting('fonnte_token');
                    if (!empty($dbToken)) {
                        $rawToken = $dbToken;
                    }
                }
            } catch (Throwable $t) {
                // Lanjutkan fallback ke environment jika koneksi database belum siap
            }
        }

        // 2. Fallback ke constant, getenv, $_ENV, $_SERVER
        if (empty($rawToken)) {
            $rawToken = (defined('FONNTE_TOKEN') ? FONNTE_TOKEN : null)
                ?: getenv('FONNTE_TOKEN') 
                ?: ($_ENV['FONNTE_TOKEN'] ?? $_SERVER['FONNTE_TOKEN'] ?? '');
        }

        // 3. Fallback ke file .env
        if (empty($rawToken)) {
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), 'FONNTE_TOKEN=') === 0) {
                        $rawToken = trim(substr(trim($line), 13));
                        break;
                    }
                }
            }
        }

        $this->token = trim((string)$rawToken);
    }

    /**
     * Memeriksa apakah token Fonnte sudah dikonfigurasi dan bukan placeholder
     */
    public function isConfigured(): bool {
        return !empty($this->token) && $this->token !== 'MASUKKAN_TOKEN_FONNTE_DI_SERVER';
    }

    /**
     * Normalisasi nomor telepon ke format internasional standar Indonesia (62xxx)
     * Contoh: 081234567890 -> 6281234567890, +62 812-3456 -> 6281234567890
     */
    public static function normalizePhoneNumber(string $phone): ?string {
        // Hapus semua karakter selain angka
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (empty($cleaned)) {
            return null;
        }

        // Jika diawali 08, ganti 0 dengan 62
        if (strpos($cleaned, '0') === 0) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (strpos($cleaned, '62') !== 0) {
            // Jika diawali 8 tanpa 0 atau 62 (misal: 81234567890)
            if (strpos($cleaned, '8') === 0) {
                $cleaned = '62' . $cleaned;
            }
        }

        // Validasi panjang nomor telepon standar Indonesia (10 - 16 digit)
        if (strlen($cleaned) < 10 || strlen($cleaned) > 16) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Memeriksa apakah nomor tujuan benar-benar terdaftar aktif di aplikasi WhatsApp
     * melalui API resmi Fonnte /validate
     *
     * @param string $targetPhone Nomor telepon (format 628... atau 08...)
     * @return bool|null true jika terdaftar, false jika tidak terdaftar, null jika gagal cek
     */
    public function isWhatsAppRegistered(string $targetPhone): ?bool {
        $normalizedPhone = self::normalizePhoneNumber($targetPhone);
        if (!$normalizedPhone || !$this->isConfigured()) {
            return null;
        }

        if (!function_exists('curl_init')) {
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->validateUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $normalizedPhone,
                'countryCode' => '0'
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
            return null;
        }

        $result = json_decode($response, true);
        if (is_array($result) && isset($result['status']) && $result['status'] === true) {
            // Cek apakah nomor ada di daftar not_registered
            if (!empty($result['not_registered']) && in_array($normalizedPhone, $result['not_registered'])) {
                return false;
            }
            // Cek apakah nomor ada di daftar registered
            if (!empty($result['registered']) && in_array($normalizedPhone, $result['registered'])) {
                return true;
            }
        }

        return null;
    }

    /**
     * Mengambil detail status perangkat pengirim Fonnte (device, status, kuota, masa aktif)
     */
    public function getDeviceDetails(): array {
        if (!$this->isConfigured() || !function_exists('curl_init')) {
            return [
                'configured' => false,
                'device' => null,
                'device_status' => 'not_configured',
                'quota' => '0',
                'name' => ''
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->deviceUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                $data['configured'] = true;
                return $data;
            }
        }

        return [
            'configured' => true,
            'device' => null,
            'device_status' => 'unknown',
            'quota' => '0',
            'name' => ''
        ];
    }

    /**
     * Mengambil nomor perangkat pengirim yang terhubung ke Fonnte jika berstatus connect
     */
    public function getConnectedDevice(): ?string {
        $details = $this->getDeviceDetails();
        if (!empty($details['device']) && ($details['device_status'] ?? '') === 'connect') {
            return (string)$details['device'];
        }
        return null;
    }

    /**
     * Mengambil QR Code koneksi WhatsApp resmi dari Fonnte API (base64 image)
     */
    public function getQrCode(): array {
        if (!$this->isConfigured() || !function_exists('curl_init')) {
            return [
                'status' => false,
                'url' => '',
                'connected' => false,
                'message' => 'Token Fonnte belum dikonfigurasi.'
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/qr',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'type' => 'qr'
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'status' => false,
            'url' => '',
            'connected' => false,
            'message' => 'Gagal mengambil QR Code dari Fonnte.'
        ];
    }

    /**
     * Memutus koneksi perangkat pengirim di Fonnte untuk pairing ulang
     */
    public function disconnectDevice(): array {
        if (!$this->isConfigured() || !function_exists('curl_init')) {
            return [
                'status' => false,
                'message' => 'Token Fonnte belum dikonfigurasi.'
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/disconnect',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'status' => false,
            'message' => 'Gagal memutuskan koneksi perangkat di Fonnte.'
        ];
    }

    /**
     * Mengirim pesan teks WhatsApp melalui endpoint resmi Fonnte API
     *
     * @param string $targetPhone Nomor WhatsApp tujuan (bisa format 08... atau 62...)
     * @param string $message Teks pesan yang dikirim
     * @return array ['success' => bool, 'message' => string, 'raw_status' => mixed]
     */
    public function sendWhatsApp(string $targetPhone, string $message): array {
        $normalizedPhone = self::normalizePhoneNumber($targetPhone);

        if (!$normalizedPhone) {
            return [
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid. Gunakan format seperti 081234567890.'
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Token API Fonnte belum dikonfigurasi di server. Silakan hubungi admin untuk mengisi FONNTE_TOKEN pada file .env.'
            ];
        }

        // Siapkan data pengiriman instan
        $postData = [
            'target' => $normalizedPhone,
            'message' => $message,
            'countryCode' => '62'
        ];

        // Pengiriman via cURL
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->token
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($response === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi server WhatsApp Fonnte: ' . ($curlError ?: 'Koneksi timeout'),
                    'http_code' => 0,
                    'provider_response' => null
                ];
            }

            $result = json_decode($response, true);

            // Validasi respons JSON dari Fonnte sesuai dokumentasi
            if (is_array($result)) {
                // Fonnte mengembalikan status: true jika pesan berhasil diantrekan / dikirim
                if (isset($result['status']) && $result['status'] === true) {
                    return [
                        'success' => true,
                        'message' => 'Pesan WhatsApp berhasil dikirim.',
                        'http_code' => $httpCode,
                        'data' => [
                            'target' => $normalizedPhone,
                            'process' => $result['process'] ?? 'queued',
                            'provider_response' => $result
                        ]
                    ];
                }

                $reason = $result['reason'] ?? $result['message'] ?? 'Respons tidak berhasil dari Fonnte.';
                return [
                    'success' => false,
                    'message' => 'Pengiriman WhatsApp gagal: ' . htmlspecialchars($reason),
                    'http_code' => $httpCode,
                    'provider_response' => $result
                ];
            }

            return [
                'success' => false,
                'message' => 'Respons tidak valid dari server Fonnte (HTTP ' . $httpCode . ').',
                'http_code' => $httpCode,
                'provider_response' => null
            ];
        }

        // Fallback file_get_contents jika cURL tidak aktif
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                             "Authorization: " . $this->token . "\r\n",
                'method'  => 'POST',
                'content' => http_build_query($postData),
                'timeout' => 20,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($this->apiUrl, false, $context);

        if ($response === false) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server WhatsApp API.',
                'http_code' => 0,
                'provider_response' => null
            ];
        }

        $result = json_decode($response, true);
        if (is_array($result) && isset($result['status']) && $result['status'] === true) {
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim.',
                'http_code' => 200,
                'data' => [
                    'target' => $normalizedPhone,
                    'process' => $result['process'] ?? 'queued',
                    'provider_response' => $result
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal mengirim pesan melalui Fonnte API: ' . ($result['reason'] ?? 'Kesalahan tidak diketahui'),
            'http_code' => 0,
            'provider_response' => $result
        ];
    }
}
