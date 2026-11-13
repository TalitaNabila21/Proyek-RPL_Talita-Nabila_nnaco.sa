<?php

require_once 'AIServiceInterface.php';

class NvidiaAIProvider implements AIServiceInterface {
    
    private $apiKey;
    private $model;
    private $temperature;
    private $maxTokens;
    private $customInstruction;

    public function __construct(string $apiKey, ?string $model = null, ?float $temperature = null, ?int $maxTokens = null, ?string $customInstruction = null) {
        $this->apiKey = $apiKey;
        $this->model = !empty($model) ? $model : 'nvidia/llama-3.1-nemotron-51b-instruct';
        $this->temperature = $temperature !== null ? max(0.0, min(1.0, (float)$temperature)) : 0.5;
        $this->maxTokens = $maxTokens !== null ? max(50, min(2048, (int)$maxTokens)) : 800;
        $this->customInstruction = $customInstruction;
    }

    public function askQuestion(string $systemContext, string $userMessage): string {
        $url = 'https://integrate.api.nvidia.com/v1/chat/completions';

        $instruction = "Kamu adalah AI Assistant resmi toko aksesoris 'nnaco_sa'.\n";
        if (!empty($this->customInstruction)) {
            $instruction .= "Catatan Tambahan Pengelola Toko: " . trim($this->customInstruction) . "\n\n";
        }
        $instruction .= "Toko nnaco_sa menjual aneka aksesoris handmade, fashion, gelang, kalung, gantungan kunci, dan perhiasan cantik.\n";
        $instruction .= "PRINSIP UTAMA: JAWAB LANGSUNG, SPESIFIK, DAN TEPAT SESUAI APA YANG DITANYAKAN PENGGUNA DENGAN MERUJUK DATA RIIL PADA 'Context Sistem'.\n";
        $instruction .= "ATURAN FORMAT JAWABAN (MUTLAK & SANGAT KETAT):\n";
        $instruction .= "1. JAWAB HANYA FAKTA DARI DATABASE SESUAI PERTANYAAN SECARA LANGSUNG.\n";
        $instruction .= "2. DILARANG KERAS MENAMBAHKAN KOMENTAR, SARAN TAMBAHAN, DISKLAIMER/NOTE, BASA-BASI, PEMBUKA, ATAU PENUTUP (DILARANG menulis 'Halo!', 'Semoga membantu', 'Ada yang bisa dibantu?', 'Catatan Penting:', 'Jika ada pertanyaan lain...', 'Apakah ada produk lain yang dicari?', dll).\n";
        $instruction .= "3. SESUAIKAN JAWABAN PERSIS DENGAN POSISI AKTOR YANG SEDANG LOGIN (Owner, Admin, Operator, Customer, atau Tamu) pada Context Sistem.\n";
        $instruction .= "4. JIKA AKTOR TIDAK MEMILIKI HAK AKSES, TOLAK DENGAN SATU KALIMAT TEGAS TANPA KOMENTAR TAMBAHAN.\n";
        $instruction .= "5. JIKA DITANYAKAN PRODUK TERTENTU (misal harga atau stok produk): JAWAB HANYA DATA PRODUK TERSEBUT SECARA SPESIFIK:\n";
        $instruction .= "   - Jika ditanya harga: Harga **[Nama Produk]**(URL): **Rp X** (Stok: Y unit).\n";
        $instruction .= "   - Jika ditanya stok: Stok **[Nama Produk]**(URL): **Y unit** (Tersedia/Habis). Harga: Rp X.\n";
        $instruction .= "   - JANGAN TAMPILKAN DAFTAR KATEGORI ATAU PRODUK LAIN YANG TIDAK DITANYAKAN!\n";
        $instruction .= "PANDUAN JAWABAN:\n";
        $instruction .= "- Pembayaran: Toko menerima Tunai / Cash (bayar di tempat / COD / saat pengambilan pesanan). Status pesanan awal 'Belum Dibayar' (Unpaid).\n";
        $instruction .= "- Cara Pesan: 1. Pilih produk di katalog, 2. Tambah ke keranjang, 3. Checkout isi WhatsApp & alamat, 4. Buat pesanan.\n";
        $instruction .= "- Kontak Toko / CS: WhatsApp Customer Care di 0819-5331-9613 ([Hubungi WhatsApp](https://wa.me/6281953319613)).\n";
        $instruction .= "- Produk & Kategori: Sebutkan produk riil, harga, stok, dan tautan langsung [Nama Produk](URL).\n";
        $instruction .= "- Privasi: DILARANG membocorkan data akun terdaftar, identitas staf pengelola, omset, atau struktur database internal jika pengguna adalah Pelanggan atau Tamu.\n\n";
        $instruction .= "Context Sistem:\n" . $systemContext;

        $data = [
            "model" => $this->model,
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

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($result === false || !empty($curlErr)) {
                throw new Exception('Gagal terhubung ke NVIDIA NIM API: ' . $curlErr);
            }
        } else {
            $options = [
                'http' => [
                    'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $this->apiKey . "\r\n",
                    'method'  => 'POST',
                    'content' => $payload,
                    'timeout' => 30,
                    'ignore_errors' => true
                ]
            ];
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);

            if ($result === false) {
                throw new Exception('Gagal menghubungi NVIDIA API via stream context.');
            }
        }

        $response = json_decode($result, true);

        if (isset($response['error'])) {
            $msg = is_array($response['error']) ? ($response['error']['message'] ?? json_encode($response['error'])) : $response['error'];
            throw new Exception('NVIDIA API Error: ' . $msg);
        }

        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }

        return "Maaf, AI Assistant NVIDIA sedang tidak dapat menjawab pertanyaan ini.";
    }
}
