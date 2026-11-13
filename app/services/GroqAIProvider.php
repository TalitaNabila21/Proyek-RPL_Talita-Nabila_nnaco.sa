<?php

require_once 'AIServiceInterface.php';

class GroqAIProvider implements AIServiceInterface {
    
    private $apiKey;
    private $model;
    private $temperature;
    private $maxTokens;
    private $customInstruction;

    public function __construct(string $apiKey, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null, ?string $customInstruction = null) {
        $this->apiKey = $apiKey;
        $this->model = !empty($model) ? $model : 'qwen/qwen3.8-27b';
        $this->temperature = $temperature !== null ? max(0.0, min(1.0, (float)$temperature)) : 0.5;
        $this->maxTokens = $maxTokens !== null ? max(50, min(2048, (int)$maxTokens)) : 500;
        $this->customInstruction = $customInstruction;
    }

    public function askQuestion(string $systemContext, string $userMessage): string {
        $url = 'https://api.groq.com/openai/v1/chat/completions';

        $instruction = "Identitas: AI Assistant resmi toko aksesoris 'nnaco_sa'.\n";
        if (!empty($this->customInstruction)) {
            $instruction .= "Catatan Pengelola: " . trim($this->customInstruction) . "\n";
        }
        $instruction .= "PRINSIP UTAMA: JAWAB LANGSUNG, SPESIFIK, DAN TEPAT SESUAI DENGAN PERTANYAAN PENGGUNA BERDASARKAN DATA FAKTUAL DI BAWAH.\n";
        $instruction .= "ATURAN MUTLAK:\n";
        $instruction .= "1. Jawab HANYA apa yang ditanyakan secara akurat sesuai database di bawah:\n";
        $instruction .= "   - Jika ditanya pesanan (pesanan apa saja, pesanan pending, pemesan, transaksi), sebutkan daftar pesanan yang cocok (nomor #ORD, nama pemesan, nominal, status, dan link detail).\n";
        $instruction .= "   - Jika ditanya daftar produk atau apa saja produk di database, sebutkan nama produk aktif, harga, dan stoknya.\n";
        $instruction .= "   - Jika ditanya customer atau pengguna terdaftar (oleh Admin/Owner), sebutkan nama-nama customer/pengguna dari data yang tersedia.\n";
        $instruction .= "   - Jika ditanya produk spesifik, jawab HANYA produk tersebut beserta harga, stok, dan link markdown-nya.\n";
        $instruction .= "2. DILARANG KERAS MENAMBAHKAN KOMENTAR, BASA-BASI, DISKLAIMER/NOTE, PEMBUKA, ATAU PENUTUP (Dilarang menulis 'Halo', 'Semoga membantu', 'Ada yang bisa dibantu?', 'Catatan:', dll).\n";
        $instruction .= "3. Patuhi hak akses aktor: data internal (omset, staf, total akun terdaftar web, transaksi pesanan toko) HANYA untuk Owner, Admin, dan Operator sesuai wewenangnya. Tolak jika Tamu atau Customer menanyakan data rahasia internal toko.\n";
        $instruction .= "4. Gunakan **bold** untuk angka/harga/status penting, dan link markdown [Nama](URL) bila merujuk produk atau halaman.\n";
        $instruction .= "5. BAHASA: WAJIB menggunakan Bahasa Indonesia yang baik dan jelas. Dilarang menggunakan Bahasa Inggris atau menolak memberikan informasi jika ditanyakan oleh Admin atau Owner, karena Admin dan Owner memiliki otorisasi penuh atas data toko.\n\n";
        $instruction .= "DATA FAKTU TOKO & SISTEM:\n" . $systemContext;

        $modelsToTry = array_unique(array_filter([
            $this->model,
            'openai/gpt-oss-20b',
            'qwen/qwen3.8-27b',
            'openai/gpt-oss-120b'
        ]));

        $lastError = '';

        foreach ($modelsToTry as $currentModel) {
            $data = [
                "model" => $currentModel,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => $instruction
                    ],
                    [
                        "role" => "user",
                        "content" => $userMessage
                    ]
                ],
                "temperature" => $this->temperature,
                "max_tokens" => $this->maxTokens
            ];

            $payload = json_encode($data);
            $result = false;
            $httpCode = 0;
            $errorMsg = '';

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $errorMsg = curl_error($ch);
                curl_close($ch);
            }

            if ($result === false || empty($result)) {
                $options = [
                    'http' => [
                        'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $this->apiKey . "\r\n",
                        'method'  => 'POST',
                        'content' => $payload,
                        'ignore_errors' => true,
                        'timeout' => 15
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ];
                $context = stream_context_create($options);
                $result = @file_get_contents($url, false, $context);
            }

            if ($result === false || empty($result)) {
                $lastError = 'Koneksi ke server AI terganggu (' . ($errorMsg ?: 'Timeout jaringan') . ').';
                continue;
            }

            $response = json_decode($result, true);

            if (isset($response['error'])) {
                $errCode = $response['error']['code'] ?? '';
                $errMessage = $response['error']['message'] ?? 'Unknown Error';
                $lastError = "Groq Model ($currentModel): " . $errMessage;

                // Jika error adalah over-capacity (503) atau rate limit (429), coba model berikutnya
                if ($httpCode === 503 || $httpCode === 429 || stripos($errMessage, 'over capacity') !== false || stripos($errMessage, 'rate limit') !== false) {
                    error_log("Groq fallback model triggered from $currentModel: $errMessage");
                    continue;
                }
                throw new Exception('Groq API Error: ' . $errMessage);
            }

            if (isset($response['choices'][0]['message']['content'])) {
                return trim($response['choices'][0]['message']['content']);
            }
        }

        if (!empty($lastError)) {
            throw new Exception($lastError);
        }

        return "Maaf, saya tidak dapat memproses pertanyaan Anda saat ini.";
    }
}
