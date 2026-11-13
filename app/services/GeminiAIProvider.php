<?php

require_once 'AIServiceInterface.php';

class GeminiAIProvider implements AIServiceInterface {
    
    private $apiKey;
    private $model;
    private $temperature;
    private $maxTokens;
    private $customInstruction;

    public function __construct(string $apiKey, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null, ?string $customInstruction = null) {
        $this->apiKey = $apiKey;
        $this->model = !empty($model) ? $model : 'gemini-1.5-flash';
        $this->temperature = $temperature !== null ? max(0.0, min(1.0, (float)$temperature)) : 0.7;
        $this->maxTokens = $maxTokens !== null ? max(50, min(2048, (int)$maxTokens)) : 500;
        $this->customInstruction = $customInstruction;
    }

    public function askQuestion(string $systemContext, string $userMessage): string {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . urlencode($this->apiKey);

        $instruction = "Kamu adalah AI Assistant resmi toko aksesoris 'nnaco_sa'.\n";
        if (!empty($this->customInstruction)) {
            $instruction .= "Catatan Tambahan Pengelola Toko: " . trim($this->customInstruction) . "\n\n";
        }
        $instruction .= "Toko nnaco_sa menjual aneka aksesoris handmade, fashion, gelang, kalung, gantungan kunci, dan perhiasan cantik.\n";
        $instruction .= "PRINSIP UTAMA: Jawab langsung, spesifik, dan tepat sesuai apa yang ditanyakan pengguna dengan merujuk data riil pada Context Sistem.\n";
        $instruction .= "ATURAN FORMAT JAWABAN (MUTLAK & SANGAT KETAT):\n";
        $instruction .= "1. JAWAB HANYA FAKTA DARI DATABASE SESUAI PERTANYAAN SECARA LANGSUNG.\n";
        $instruction .= "2. DILARANG KERAS MENAMBAHKAN KOMENTAR, SARAN TAMBAHAN, DISKLAIMER/NOTE, BASA-BASI, PEMBUKA, ATAU PENUTUP (DILARANG menulis 'Halo!', 'Semoga membantu', 'Ada yang bisa dibantu?', 'Catatan Penting:', 'Jika ada pertanyaan lain...', dll).\n";
        $instruction .= "3. SESUAIKAN JAWABAN PERSIS DENGAN POSISI AKTOR YANG SEDANG LOGIN (Owner, Admin, Operator, Customer, atau Tamu) pada Context Sistem.\n";
        $instruction .= "4. JIKA AKTOR TIDAK MEMILIKI HAK AKSES, TOLAK DENGAN SATU KALIMAT TEGAS TANPA KOMENTAR TAMBAHAN.\n";
        $instruction .= "PANDUAN JAWABAN:\n";
        $instruction .= "- Pembayaran: Toko menerima Tunai / Cash (bayar di tempat / COD / saat pengambilan pesanan). Status pesanan awal 'Belum Dibayar' (Unpaid).\n";
        $instruction .= "- Cara Pesan: 1. Pilih produk di katalog, 2. Tambah ke keranjang, 3. Buka keranjang, 4. Checkout isi WhatsApp & alamat, 5. Buat pesanan.\n";
        $instruction .= "- Kontak Toko / CS: WhatsApp Customer Care di 0819-5331-9613 ([Hubungi WhatsApp](https://wa.me/6281953319613)).\n";
        $instruction .= "- Produk & Kategori: JAWAB HANYA PRODUK YANG DITANYAKAN (sebutkan harga dan stok produk tersebut serta link-nya). DILARANG menampilkan produk atau kategori lain yang tidak ditanyakan.\n";
        $instruction .= "- Privasi: DILARANG membocorkan data akun internal toko, staf toko, omset, atau struktur database jika pengguna adalah Pelanggan/Tamu.\n\n";
        $instruction .= "Context Sistem:\n" . $systemContext . "\n\n";
        $instruction .= "Pesan Pengguna: " . $userMessage;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $instruction]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => $this->temperature,
                "maxOutputTokens" => $this->maxTokens,
            ]
        ];

        $payload = json_encode($data);

        // Prioritize cURL for reliable network communication
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 25);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($result === false || !empty($curlErr)) {
                throw new Exception('Gagal terhubung ke Gemini API: ' . $curlErr);
            }
        } else {
            $options = [
                'http' => [
                    'header'  => "Content-type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => $payload,
                    'timeout' => 25,
                    'ignore_errors' => true
                ]
            ];
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);

            if ($result === false) {
                throw new Exception('Gagal menghubungi Gemini API via stream context.');
            }
        }

        $response = json_decode($result, true);

        if (isset($response['error'])) {
            $msg = $response['error']['message'] ?? 'Unknown error';
            throw new Exception('Gemini API Error: ' . $msg);
        }

        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($response['candidates'][0]['content']['parts'][0]['text']);
        }

        return "Maaf, tidak ada jawaban dari Gemini untuk pertanyaan tersebut.";
    }
}
