<?php
require_once __DIR__ . '/../services/GroqAIProvider.php';
require_once __DIR__ . '/../services/GeminiAIProvider.php';
require_once __DIR__ . '/../services/NvidiaAIProvider.php';

class AIChatController extends Controller {

    public function index() {
        return $this->ask();
    }

    public function ask() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ambil input pengguna dari JSON request
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, TRUE);
            $userMessage = isset($input['message']) ? htmlspecialchars(trim($input['message'])) : '';

            if (empty($userMessage)) {
                echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
                return;
            }

            // Ambil data langsung dari Database (Real-Time Live Data)
            $userModel = $this->model('User');
            $categoryModel = $this->model('Category');
            $productModel = $this->model('Product');
            $orderModel = $this->model('Order');
            $settingModel = $this->model('Setting');

            // 1. Identifikasi Posisi Aktor yang Sedang Login
            $currentUser = null;
            if (isset($_SESSION['user_id'])) {
                $currentUser = $userModel->getUserById($_SESSION['user_id']);
            }
            $userRole = strtolower((string)($_SESSION['user_role'] ?? ($currentUser['role'] ?? 'guest')));

            // 2. Data Kategori & Produk (Publik)
            $activeCategories = $categoryModel->getActive();
            $totalCategories = count($activeCategories);
            $categoryNames = array_column($activeCategories, 'name');

            $activeProducts = $productModel->getActive();
            $totalProducts = count($activeProducts);
            $prices = !empty($activeProducts) ? array_column($activeProducts, 'price') : [0];
            $minPrice = !empty($prices) ? min($prices) : 0;
            $maxPrice = !empty($prices) ? max($prices) : 0;

            // 3. Data Kontak & Pengaturan Toko
            $rawStorePhone = $settingModel->getSetting('order_notification_phone', '6281953319613');
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawStorePhone);
            if (substr($cleanPhone, 0, 1) === '0') {
                $waNumber = '62' . substr($cleanPhone, 1);
                $displayPhone = $cleanPhone;
            } elseif (substr($cleanPhone, 0, 2) === '62') {
                $waNumber = $cleanPhone;
                $displayPhone = '0' . substr($cleanPhone, 2);
            } else {
                $waNumber = '6281953319613';
                $displayPhone = '0819-5331-9613';
            }
            $waLink = "https://wa.me/" . $waNumber;

            // 4. Data Transaksi & Manajemen Sesuai Hak Akses Aktor
            $allUsers = [];
            $userStats = [
                'total' => 0,
                'can_login_total' => 0,
                'cannot_login_total' => 0,
                'can_login_by_role' => []
            ];
            $totalOrders = 0;
            $allOrders = [];
            $totalRevenue = 0;
            $orderStats = [
                'total_pending' => 0,
                'total_processing' => 0,
                'total_shipped' => 0,
                'total_completed' => 0
            ];

            // Owner dan Admin memiliki akses ke data pengguna & pesanan lengkap
            if (in_array($userRole, ['owner', 'admin'])) {
                $allUsers = $userModel->getAllUsers();
                $userStats = $userModel->getUserStats();
                $totalOrders = $orderModel->getTotalOrdersCount();
                $allOrders = $orderModel->getAllOrders();
                $dbOrderStats = $orderModel->getOrderStats();
                if ($dbOrderStats) {
                    $orderStats = $dbOrderStats;
                }
                foreach ($allOrders as $o) {
                    $totalRevenue += (float)$o['total_amount'];
                }
            } elseif ($userRole === 'operator') {
                // Operator memiliki akses ke data operasional pesanan dan stok
                $totalOrders = $orderModel->getTotalOrdersCount();
                $allOrders = $orderModel->getAllOrders();
                $dbOrderStats = $orderModel->getOrderStats();
                if ($dbOrderStats) {
                    $orderStats = $dbOrderStats;
                }
            }

            // Pesanan milik pengguna yang sedang login (untuk customer)
            $userOrders = [];
            if ($currentUser) {
                $userOrders = $orderModel->getOrdersByUserId($currentUser['id']);
            }

            // 5. Bangun Konteks Pengetahuan Toko & Identitas Aktor
            // 5. Bangun Konteks Pengetahuan Toko & Identitas Aktor (Ringkas, Padat, Hemat Token)
            $storeKnowledge = "INFO TOKO NNACO_SA:\n" .
                "- Profil: Toko online aksesoris handmade, fashion, dan perhiasan cantik (Banjarmasin).\n" .
                "- Jam Operasional: Setiap Hari (08:00 - 21:00 WIB). Layanan pemesanan website 24 jam.\n" .
                "- Lokasi Toko: Banjarmasin, melayani pengiriman kurir lokal/ekspedisi dan opsi ambil langsung di tempat (konfirmasi via WA).\n" .
                "- Kontak Layanan WhatsApp: [Hubungi WhatsApp Customer Care]({$waLink}) ({$displayPhone})\n" .
                "- Metode Pembayaran: Tunai / Cash (bayar di tempat / COD / saat pengambilan pesanan). Belum menerima transfer bank atau QRIS.\n" .
                "- Panduan Cara Memesan: 1. Pilih produk di [Katalog Produk](" . BASEURL . "/product) -> 2. Tambah ke [Keranjang Belanja](" . BASEURL . "/cart) -> 3. Isi formulir [Checkout](" . BASEURL . "/checkout) -> 4. Klik Buat Pesanan.\n" .
                "- Pengiriman & Pelacakan: Melalui kurir lokal atau ekspedisi. Status pesanan dapat dicek di [Riwayat Pesanan](" . BASEURL . "/CustomerController/orders).\n" .
                "- Batal/Ubah Pesanan: Hubungi [WhatsApp Customer Care]({$waLink}) dengan nomor pesanan.\n" .
                "- Pendaftaran & Login: Daftar akun di [Daftar Akun Baru](" . BASEURL . "/auth/register), masuk di [Login](" . BASEURL . "/auth/login).\n" .
                "- Pengaturan: Ganti Password akun dan atur Dark/Light Mode di menu [Pengaturan](" . BASEURL . "/settings).\n\n";

            $catalogKnowledge = "KATALOG PRODUK TOKO ({$totalCategories} kategori, {$totalProducts} produk aktif):\n";
            $catalogKnowledge .= "Kategori: " . implode(', ', $categoryNames) . "\n";
            $catalogKnowledge .= "Daftar Produk:\n";
            foreach ($activeProducts as $p) {
                $stockInfo = $p['stock'] > 0 ? "Stok {$p['stock']} unit" : "Stok Habis";
                $catalogKnowledge .= "- [{$p['name']}](" . BASEURL . "/product/detail/{$p['slug']}) | Rp " . number_format($p['price'], 0, ',', '.') . " | {$stockInfo} | Kat: {$p['category_name']}\n";
            }
            $catalogKnowledge .= "\n";

            // Bangun Konteks Berdasarkan Posisi Aktor yang Login
            $actorContext = "POSISI & HAK AKSES AKTOR YANG SEDANG LOGIN:\n";
            if ($userRole === 'owner') {
                $actorContext .= "- Posisi: OWNER (PEMILIK TOKO) - {$currentUser['name']} ({$currentUser['email']})\n";
                $actorContext .= "- Hak Akses: Penuh seluruh data keuangan, omset, pesanan, dan akun pengguna.\n";
                $actorContext .= "- Data Toko: Omset Rp " . number_format($totalRevenue, 0, ',', '.') . " ({$totalOrders} pesanan). Pengguna: {$userStats['total']} orang (Bisa Login: {$userStats['can_login_total']}).\n";
                $actorContext .= "- Rincian Pesanan: Pending: " . ($orderStats['total_pending'] ?? 0) . ", Processing: " . ($orderStats['total_processing'] ?? 0) . ", Shipped: " . ($orderStats['total_shipped'] ?? 0) . ", Completed: " . ($orderStats['total_completed'] ?? 0) . "\n";
                if (!empty($allOrders)) {
                    $actorContext .= "- Transaksi Pesanan Terbaru (Kelola: " . BASEURL . "/AdminOrderController):\n";
                    foreach (array_slice($allOrders, 0, 8) as $ao) {
                        $actorContext .= "  * #{$ao['order_number']} | Pemesan: {$ao['shipping_name']} | Rp " . number_format($ao['total_amount'], 0, ',', '.') . " | Status: {$ao['status']} | " . BASEURL . "/adminOrder/detail/{$ao['id']}\n";
                    }
                }
                $customers = array_filter($allUsers, function($u) { return ($u['role'] ?? '') === 'customer'; });
                if (!empty($customers)) {
                    $custNames = array_column($customers, 'name');
                    $actorContext .= "- Daftar Customer Terdaftar (" . count($customers) . " orang): " . implode(', ', $custNames) . "\n";
                }
            } elseif ($userRole === 'admin') {
                $actorContext .= "- Posisi: ADMIN (ADMINISTRATOR) - {$currentUser['name']} ({$currentUser['email']})\n";
                $actorContext .= "- Hak Akses: Kelola produk, pesanan, operasional, dan akun pengguna.\n";
                $actorContext .= "- Data Toko: Omset Rp " . number_format($totalRevenue, 0, ',', '.') . " ({$totalOrders} pesanan). Pengguna: {$userStats['total']} orang (Bisa Login: {$userStats['can_login_total']}).\n";
                $actorContext .= "- Rincian Pesanan: Pending: " . ($orderStats['total_pending'] ?? 0) . ", Processing: " . ($orderStats['total_processing'] ?? 0) . ", Shipped: " . ($orderStats['total_shipped'] ?? 0) . ", Completed: " . ($orderStats['total_completed'] ?? 0) . "\n";
                if (!empty($allOrders)) {
                    $actorContext .= "- Transaksi Pesanan Terbaru (Kelola: " . BASEURL . "/AdminOrderController):\n";
                    foreach (array_slice($allOrders, 0, 8) as $ao) {
                        $actorContext .= "  * #{$ao['order_number']} | Pemesan: {$ao['shipping_name']} | Rp " . number_format($ao['total_amount'], 0, ',', '.') . " | Status: {$ao['status']} | " . BASEURL . "/adminOrder/detail/{$ao['id']}\n";
                    }
                }
                $customers = array_filter($allUsers, function($u) { return ($u['role'] ?? '') === 'customer'; });
                if (!empty($customers)) {
                    $custNames = array_column($customers, 'name');
                    $actorContext .= "- Daftar Customer Terdaftar (" . count($customers) . " orang): " . implode(', ', $custNames) . "\n";
                }
            } elseif ($userRole === 'operator') {
                $actorContext .= "- Posisi: OPERATOR (PESANAN & GUDANG) - {$currentUser['name']} ({$currentUser['email']})\n";
                $actorContext .= "- Hak Akses: Kelola transaksi pesanan dan stok. TIDAK berhak akses omset dan akun pengguna.\n";
                $actorContext .= "- Total Pesanan: {$totalOrders} pesanan (Pending: " . ($orderStats['total_pending'] ?? 0) . ", Processing: " . ($orderStats['total_processing'] ?? 0) . ", Shipped: " . ($orderStats['total_shipped'] ?? 0) . ", Completed: " . ($orderStats['total_completed'] ?? 0) . ").\n";
                if (!empty($allOrders)) {
                    $actorContext .= "- Transaksi Pesanan Operasional (Kelola: " . BASEURL . "/AdminOrderController):\n";
                    foreach (array_slice($allOrders, 0, 8) as $ao) {
                        $actorContext .= "  * #{$ao['order_number']} | Pemesan: {$ao['shipping_name']} | Rp " . number_format($ao['total_amount'], 0, ',', '.') . " | Status: {$ao['status']} | " . BASEURL . "/adminOrder/detail/{$ao['id']}\n";
                    }
                }
            } elseif ($userRole === 'customer') {
                $actorContext .= "- Posisi: PELANGGAN (CUSTOMER) - {$currentUser['name']} ({$currentUser['email']})\n";
                $actorContext .= "- Status: SEDANG LOGIN. Dilarang akses data rahasia internal toko (omset, staf, akun lain).\n";
                if (!empty($userOrders)) {
                    $actorContext .= "- Riwayat Pesanan Pribadi (" . count($userOrders) . " pesanan):\n";
                    foreach ($userOrders as $uo) {
                        $actorContext .= "  * Order #{$uo['order_number']} | Status: {$uo['status']} | Total: Rp " . number_format($uo['total_amount'], 0, ',', '.') . " | " . BASEURL . "/CustomerController/orderDetail/{$uo['id']}\n";
                    }
                } else {
                    $actorContext .= "- Riwayat Pesanan: Belum ada pesanan belanja.\n";
                }
            } else {
                $actorContext .= "- Posisi: TAMU (GUEST / BELUM LOGIN)\n";
                $actorContext .= "- Status: Belum login. Dilarang akses seluruh data rahasia internal toko (omset, staf, akun lain).\n";
            }
            $actorContext .= "\n";

            $context = $actorContext . $storeKnowledge . $catalogKnowledge;

            // 1. Jalankan Real-Time Database Response Engine terlebih dahulu (Akurat 100%, 0 lag, hemat token)
            $dbResponse = $this->getDatabaseFallbackResponse(
                $userMessage,
                $allUsers,
                $userStats,
                $totalCategories,
                $categoryNames,
                $activeCategories,
                $totalProducts,
                $activeProducts,
                $totalOrders,
                $allOrders,
                $totalRevenue,
                $orderStats,
                $currentUser,
                $userOrders,
                $orderModel,
                $userRole,
                $minPrice,
                $maxPrice,
                $displayPhone,
                $waLink,
                true // Kembalikan null jika pertanyaan adalah percakapan terbuka/non-database
            );

            if ($dbResponse !== null) {
                $cleanDbResponse = $this->stripExtraneousComments($dbResponse);
                echo json_encode([
                    'status' => 'success',
                    'response' => nl2br(htmlspecialchars($cleanDbResponse))
                ]);
                return;
            }

            // 2. Jika bukan kueri data spesifik, gunakan AI Provider (Groq / Gemini / Nvidia)
            $aiService = $this->resolveAiService();
            if ($aiService) {
                try {
                    $response = $aiService->askQuestion($context, $userMessage);
                    // Tolak jika AI menolak dalam bahasa Inggris / template penolakan
                    if (!preg_match('/^(?:I(?: am|\'m)? sorry|I cannot|As an AI|I am unable)/i', trim($response))) {
                        $cleanResponse = $this->stripExtraneousComments($response);
                        if (!empty($cleanResponse)) {
                            echo json_encode([
                                'status' => 'success',
                                'response' => nl2br(htmlspecialchars($cleanResponse))
                            ]);
                            return;
                        }
                    }
                } catch (Exception $e) {
                    error_log('AI Provider fallback triggered: ' . $e->getMessage());
                }
            }

            // 3. Fallback default sesuai posisi aktor jika AI Provider tidak aktif atau gagal
            $fallbackDefault = $this->getActorDefaultSummary($userRole, $totalProducts, $totalCategories, $userStats, $totalOrders, $totalRevenue, $orderStats, $minPrice, $maxPrice);
            $cleanFallback = $this->stripExtraneousComments($fallbackDefault);

            echo json_encode([
                'status' => 'success',
                'response' => nl2br(htmlspecialchars($cleanFallback))
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        }
    }

    /**
     * Membersihkan komentar atau basa-basi pembuka, penutup, dan catatan tambahan
     */
    private function stripExtraneousComments($text) {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", (string)$text));
        $filtered = [];

        foreach ($lines as $i => $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $filtered[] = '';
                continue;
            }

            // Saring sapaan pembuka di awal kalimat jika ada teks lanjutan
            if ($i <= 1 && preg_match('/^(?:halo|hai|selamat\s+(?:pagi|siang|sore|malam))[!,\.]*(?:\s+(?:admin|owner|kak|kakak|pelanggan|teman|pengunjung))?[!,\.]*\s*/i', $trimmed, $m)) {
                $trimmed = trim(substr($trimmed, strlen($m[0])));
                if ($trimmed === '' && count($lines) > 1) {
                    continue;
                }
            }

            // Saring catatan / disclaimer yang tidak diminta
            if (preg_match('/^(?:💡\s*)?(?:catatan\s*penting|catatan|note)\s*:/i', $trimmed)) {
                continue;
            }

            // Hapus penutup basa-basi tanpa menghapus konten utama baris
            $trimmed = preg_replace('/(?:ada\s+yang\s+bisa\s+(?:saya|aku|kami)?\s*di?bantu|bisa\s+saya\s+bantu|apakah\s+ada\s+(?:hal|produk|yang|pertanyaan)|ada\s+pertanyaan\s+lain|jika\s+(?:ada|memiliki)\s+pertanyaan|semoga\s+(?:informasi\s+ini\s+)?(?:membantu|bermanfaat)|yuk\s+(?:temukan|jelajahi|belanja)|selamat\s+berbelanja)[^\.\n]*[\.\!]*/i', '', $trimmed);
            $trimmed = trim($trimmed);

            if ($trimmed === '') {
                continue;
            }

            $filtered[] = $trimmed;
        }

        $result = trim(implode("\n", $filtered));
        if (empty($result)) {
            return "Halo! AI Assistant nnaco_sa siap membantu Anda mengecek katalog produk aksesoris, harga, ketersediaan stok, panduan pemesanan, dan layanan toko. Silakan tanyakan hal yang ingin Anda ketahui.";
        }
        return preg_replace('/\n{3,}/', "\n\n", $result);
    }

    /**
     * Database Response Engine yang menjawab pertanyaan secara tepat, langsung, tanpa komentar, sesuai posisi aktor
     */
    private function getDatabaseFallbackResponse(
        $message,
        $allUsers,
        $userStats,
        $totalCategories,
        $categoryNames,
        $activeCategories,
        $totalProducts,
        $activeProducts,
        $totalOrders,
        $allOrders,
        $totalRevenue,
        $orderStats,
        $currentUser,
        $userOrders,
        $orderModel,
        $userRole = 'guest',
        $minPrice = 0,
        $maxPrice = 0,
        $displayPhone = '0819-5331-9613',
        $waLink = 'https://wa.me/6281953319613',
        $returnNullOnNoMatch = false
    ) {
        if ($userRole === true) {
            $userRole = 'admin';
        } elseif ($userRole === false || empty($userRole)) {
            $userRole = 'guest';
        }
        $userRole = strtolower((string)$userRole);
        $msg = strtolower(trim($message));

        // 0. Sapaan Ramah / Greetings
        if (preg_match('/^(?:halo|hai|hey|hei|p|tes|test|selamat\s+(?:pagi|siang|sore|malam))[!,\.\?\s]*$/i', $msg)) {
            return "Halo! AI Assistant nnaco_sa siap membantu Anda mengecek katalog produk aksesoris, harga, ketersediaan stok, panduan cara pemesanan, dan informasi layanan toko. Silakan tanyakan hal yang ingin Anda ketahui.";
        }

        // 0.5. Fitur, Kemampuan AI, Menu, Bantuan, & Identitas AI
        if (strpos($msg, 'siapa kamu') !== false || strpos($msg, 'kamu siapa') !== false || strpos($msg, 'siapa anda') !== false) {
            return "Saya adalah **AI Assistant resmi toko aksesoris nnaco_sa**, siap membantu memberikan informasi faktual dan akurat langsung dari database toko mengenai produk, stok, pesanan, akun, dan layanan toko.";
        }

        if (
            strpos($msg, 'bisa bantu apa') !== false ||
            strpos($msg, 'kamu bisa apa') !== false ||
            strpos($msg, 'bisa apa saja') !== false ||
            strpos($msg, 'apa yang bisa kamu lakukan') !== false ||
            strpos($msg, 'bantuan') !== false ||
            strpos($msg, 'panduan') !== false ||
            strpos($msg, 'fitur apa') !== false ||
            strpos($msg, 'fitur-fitur') !== false ||
            preg_match('/^(?:fitur|menu|help|bantuan)[!,\.\?\s]*$/i', $msg) ||
            (strpos($msg, 'menu') !== false && (strpos($msg, 'apa') !== false || strpos($msg, 'saja') !== false || strpos($msg, 'toko') !== false))
        ) {
            if ($userRole === 'admin') {
                return "Fitur AI Assistant nnaco_sa untuk **Admin**:\n" .
                       "- **Manajemen Pengguna**: Cek total pengguna, akun aktif/nonaktif, daftar customer, atau panduan tambah user di [Manajemen Pengguna](" . BASEURL . "/AdminUserController).\n" .
                       "- **Manajemen Produk**: Cek 34 produk aktif, harga, stok unit, atau panduan tambah produk di [Manajemen Produk](" . BASEURL . "/AdminProductController).\n" .
                       "- **Manajemen Pesanan**: Cek transaksi masuk, pesanan pending/confirmed, rincian pesanan #ORD, pemesan, atau panduan proses order di [Manajemen Pesanan](" . BASEURL . "/AdminOrderController).\n" .
                       "- **Laporan & Keuangan**: Cek total omset dan performa penjualan di [Dashboard Admin](" . BASEURL . "/admin).\n" .
                       "- **Layanan Toko**: Cek kontak WhatsApp resmi, metode pembayaran tunai/COD, dan jam operasional.";
            } elseif ($userRole === 'owner') {
                return "Fitur AI Assistant nnaco_sa untuk **Owner**:\n" .
                       "- **Laporan & Omset**: Cek total omset transaksi dan performa toko di [Dashboard Owner](" . BASEURL . "/owner).\n" .
                       "- **Pesanan**: Pantau total transaksi dan status pesanan (pending, processing, shipped, completed).\n" .
                       "- **Pengguna**: Cek total pengguna terdaftar, akun bisa login, dan data customer.\n" .
                       "- **Katalog & Stok**: Cek daftar produk aktif, harga, dan ketersediaan stok.";
            } elseif ($userRole === 'operator') {
                return "Fitur AI Assistant nnaco_sa untuk **Operator**:\n" .
                       "- **Operasional Pesanan**: Cek pesanan masuk, pesanan pending, dan rincian transaksi #ORD di [Dashboard Operator](" . BASEURL . "/operator).\n" .
                       "- **Pengiriman**: Panduan konfirmasi pengiriman pesanan dan input nomor resi kurir.\n" .
                       "- **Katalog & Stok**: Cek ketersediaan stok unit dan harga produk di toko.";
            } elseif ($userRole === 'customer') {
                return "Fitur AI Assistant nnaco_sa untuk **Pelanggan**:\n" .
                       "- **Katalog Produk**: Cek koleksi aksesoris handmade, harga, dan stok di [Katalog Produk](" . BASEURL . "/product).\n" .
                       "- **Rekomendasi**: Rekomendasi produk termurah, termahal, atau rekomendasi kado.\n" .
                       "- **Riwayat Belanja**: Cek status pesanan belanja Anda di [Riwayat Pesanan](" . BASEURL . "/CustomerController/orders).\n" .
                       "- **Panduan Belanja**: Cara pemesanan, pembayaran tunai/COD, pengiriman kurir, dan kontak WhatsApp toko.";
            } else {
                return "AI Assistant nnaco_sa dapat membantu Anda mengecek katalog produk aksesoris, harga, ketersediaan stok, panduan pemesanan, metode pembayaran tunai/COD, informasi pengiriman, kontak WhatsApp toko, serta panduan pendaftaran akun baru di [Daftar Akun](" . BASEURL . "/auth/register).";
            }
        }

        // 1. Customer Support, WhatsApp, & Kontak Toko
        if (
            strpos($msg, 'whatsapp') !== false ||
            strpos($msg, 'no wa') !== false ||
            strpos($msg, 'nomor wa') !== false ||
            strpos($msg, 'wa admin') !== false ||
            strpos($msg, 'chat wa') !== false ||
            preg_match('/\b(wa|cs)\b/i', $msg) ||
            strpos($msg, 'kontak') !== false ||
            strpos($msg, 'hubungi') !== false ||
            strpos($msg, 'customer service') !== false ||
            strpos($msg, 'call center') !== false ||
            strpos($msg, 'telepon') !== false ||
            strpos($msg, 'nomor admin') !== false ||
            strpos($msg, 'nomor toko') !== false ||
            strpos($msg, 'kontak toko') !== false
        ) {
            return "Layanan WhatsApp resmi nnaco_sa:\n" .
                   "- No. WhatsApp: **{$displayPhone}**\n" .
                   "- Link: [Hubungi WhatsApp Customer Care]({$waLink})\n" .
                   "- Jam Operasional: Setiap Hari (08:00 - 21:00 WIB)";
        }

        // 2. Pertanyaan Metode Pembayaran
        if (
            strpos($msg, 'bayar') !== false ||
            strpos($msg, 'metode pembayaran') !== false ||
            strpos($msg, 'cara bayar') !== false ||
            strpos($msg, 'transfer') !== false ||
            strpos($msg, 'bank') !== false ||
            strpos($msg, 'cod') !== false ||
            strpos($msg, 'cash') !== false ||
            strpos($msg, 'tunai') !== false ||
            strpos($msg, 'qris') !== false ||
            strpos($msg, 'pembayaran') !== false
        ) {
            if (strpos($msg, 'transfer') !== false || strpos($msg, 'qris') !== false || strpos($msg, 'bank') !== false) {
                return "Saat ini toko nnaco_sa hanya menerima pembayaran secara **Tunai / Cash** (bayar di tempat / COD / saat pengambilan pesanan). Pembayaran transfer bank atau QRIS belum tersedia.";
            }
            return "Metode pembayaran di toko nnaco_sa:\n" .
                   "- **Tunai / Cash** (Bayar di tempat / COD / saat pengambilan pesanan).\n" .
                   "- Status transaksi awal: **Belum Dibayar (Unpaid)**.\n" .
                   "- Pembayaran dilunasi tunai saat pesanan diserahkan atau diambil.";
        }

        // 2.5. Panduan Memproses Pesanan untuk Admin & Operator
        $isProcessOrderGuide = (
            strpos($msg, 'cara proses') !== false ||
            strpos($msg, 'memproses pesanan') !== false ||
            strpos($msg, 'proses pesanan') !== false ||
            strpos($msg, 'kelola pesanan') !== false ||
            strpos($msg, 'konfirmasi pesanan') !== false ||
            strpos($msg, 'cara konfirmasi') !== false ||
            strpos($msg, 'cara kirim pesanan') !== false ||
            strpos($msg, 'kirim pesanan') !== false ||
            strpos($msg, 'input resi') !== false ||
            strpos($msg, 'masukkan resi') !== false ||
            strpos($msg, 'ubah status pesanan') !== false ||
            strpos($msg, 'ganti status pesanan') !== false
        );

        if ($isProcessOrderGuide) {
            if (in_array($userRole, ['owner', 'admin', 'operator'])) {
                return "Panduan memproses pesanan masuk (Akses Admin & Operator):\n" .
                       "1. Buka menu [Manajemen Pesanan](" . BASEURL . "/AdminOrderController).\n" .
                       "2. Pada pesanan dengan status **Pending**, klik tombol **Detail**.\n" .
                       "3. Lakukan konfirmasi pesanan dan pembayaran tunai saat transaksi disepakati.\n" .
                       "4. Saat barang dikirim, pilih nama ekspedisi/kurir, masukkan nomor resi, lalu ubah status pesanan menjadi **Shipped**.\n" .
                       "5. Setelah pesanan diterima pembeli, ubah status menjadi **Completed**.";
            } else {
                return "Status pesanan belanja Anda dapat dipantau di menu [Riwayat Pesanan](" . BASEURL . "/CustomerController/orders). Untuk konfirmasi pembayaran atau pengiriman, hubungi [WhatsApp Customer Care]({$waLink}).";
            }
        }

        // 3. Panduan Cara Pemesanan / Cara Beli
        if (
            strpos($msg, 'cara pesan') !== false ||
            strpos($msg, 'cara memesan') !== false ||
            strpos($msg, 'cara beli') !== false ||
            strpos($msg, 'cara membeli') !== false ||
            strpos($msg, 'cara order') !== false ||
            strpos($msg, 'bagaimana cara pesan') !== false ||
            strpos($msg, 'bagaimana cara beli') !== false ||
            strpos($msg, 'bagaimana cara membeli') !== false ||
            strpos($msg, 'bagaimana cara memesan') !== false ||
            strpos($msg, 'cara belanja') !== false ||
            strpos($msg, 'cara checkout') !== false ||
            strpos($msg, 'langkah pesan') !== false ||
            strpos($msg, 'langkah beli') !== false ||
            strpos($msg, 'langkah pemesanan') !== false ||
            strpos($msg, 'gimana cara') !== false ||
            (strpos($msg, 'cara') !== false && (strpos($msg, 'pesan') !== false || strpos($msg, 'beli') !== false || strpos($msg, 'order') !== false))
        ) {
            return "Cara memesan produk di nnaco_sa:\n" .
                   "1. Pilih produk di [Katalog Produk](" . BASEURL . "/product).\n" .
                   "2. Klik tombol **Tambah ke Keranjang**.\n" .
                   "3. Periksa pesanan di [Keranjang Belanja](" . BASEURL . "/cart).\n" .
                   "4. Masuk ke [Checkout](" . BASEURL . "/checkout) dan isi nama, nomor WhatsApp aktif, serta alamat pengiriman.\n" .
                   "5. Klik tombol **Buat Pesanan**.";
        }

        // 4. Pengiriman & Pengambilan Pesanan
        if (
            strpos($msg, 'pengiriman') !== false ||
            strpos($msg, 'ongkir') !== false ||
            strpos($msg, 'kirim') !== false ||
            strpos($msg, 'kurir') !== false ||
            strpos($msg, 'ekspedisi') !== false ||
            strpos($msg, 'ambil di tempat') !== false ||
            strpos($msg, 'bisa dikirim') !== false ||
            strpos($msg, 'bisa kirim') !== false
        ) {
            return "Informasi pengiriman dan pengambilan pesanan nnaco_sa:\n" .
                   "- Pengiriman kurir: Dikirim ke alamat penerima melalui kurir lokal atau ekspedisi.\n" .
                   "- Ambil di tempat: Dapat diambil langsung di lokasi toko dengan konfirmasi WhatsApp.\n" .
                   "- Pelacakan status: Nomor resi dan status pengiriman dapat dilihat di [Riwayat Pesanan](" . BASEURL . "/CustomerController/orders).\n" .
                   "- Estimasi ongkir: Hubungi [WhatsApp Customer Care]({$waLink}) ({$displayPhone}).";
        }

        // 4.1. Jam Buka & Jam Operasional
        if (
            strpos($msg, 'jam buka') !== false ||
            strpos($msg, 'jam operasional') !== false ||
            strpos($msg, 'buka jam') !== false ||
            strpos($msg, 'tutup jam') !== false ||
            strpos($msg, 'buka kapan') !== false ||
            strpos($msg, 'hari minggu') !== false ||
            strpos($msg, 'jadwal') !== false ||
            (strpos($msg, 'jam') !== false && (strpos($msg, 'buka') !== false || strpos($msg, 'toko') !== false || strpos($msg, 'operasional') !== false))
        ) {
            return "Jam operasional layanan toko nnaco_sa: **Setiap Hari (08:00 - 21:00 WIB)**. Pembelian online melalui website dapat dilakukan selama 24 jam.";
        }

        // 4.2. Lokasi & Alamat Toko
        if (
            strpos($msg, 'alamat') !== false ||
            strpos($msg, 'lokasi toko') !== false ||
            strpos($msg, 'dimana toko') !== false ||
            strpos($msg, 'toko dimana') !== false ||
            strpos($msg, 'posisi toko') !== false ||
            strpos($msg, 'lokasi') !== false
        ) {
            return "Toko nnaco_sa melayani pembelian online melalui website dengan pengiriman kurir lokal. Untuk informasi pengambilan langsung di tempat atau detail lokasi toko, silakan hubungi [WhatsApp Customer Care]({$waLink}) ({$displayPhone}).";
        }

        // 4.3. Pendaftaran Akun Baru
        if (
            strpos($msg, 'daftar akun') !== false ||
            strpos($msg, 'buat akun') !== false ||
            strpos($msg, 'bikin akun') !== false ||
            strpos($msg, 'registrasi') !== false ||
            strpos($msg, 'register') !== false ||
            (strpos($msg, 'cara') !== false && strpos($msg, 'daftar') !== false)
        ) {
            return "Langkah mendaftar akun baru di nnaco_sa:\n" .
                   "1. Buka halaman [Daftar Akun Baru](" . BASEURL . "/auth/register).\n" .
                   "2. Lengkapi data: Nama Lengkap, Email, No. WhatsApp aktif, dan Kata Sandi.\n" .
                   "3. Klik tombol **Daftar**.";
        }

        // 4.4. Login / Masuk Akun
        if (
            strpos($msg, 'cara login') !== false ||
            strpos($msg, 'cara masuk') !== false ||
            strpos($msg, 'login akun') !== false ||
            strpos($msg, 'masuk akun') !== false
        ) {
            return "Langkah masuk (login) ke akun nnaco_sa:\n" .
                   "1. Buka halaman [Login](" . BASEURL . "/auth/login).\n" .
                   "2. Masukkan Email dan Kata Sandi Anda.\n" .
                   "3. Klik tombol **Masuk**.";
        }

        // 4.5. Batal Pesanan / Retur
        if (
            strpos($msg, 'batal') !== false ||
            strpos($msg, 'batalkan') !== false ||
            strpos($msg, 'cancel') !== false ||
            strpos($msg, 'retur') !== false ||
            strpos($msg, 'pengembalian') !== false
        ) {
            return "Untuk pembatalan atau perubahan pesanan, silakan segera hubungi admin melalui [WhatsApp Customer Care]({$waLink}) ({$displayPhone}) dengan menyertakan nomor pesanan Anda.";
        }

        // 4.6. Keranjang Belanja
        if (strpos($msg, 'keranjang') !== false || strpos($msg, 'cart') !== false) {
            return "Keranjang belanja Anda dapat diakses di menu [Keranjang Belanja](" . BASEURL . "/cart).";
        }

        // 4.7. Promo & Diskon
        if (strpos($msg, 'promo') !== false || strpos($msg, 'diskon') !== false || strpos($msg, 'potongan harga') !== false) {
            return "Toko nnaco_sa menyediakan produk berkualitas dengan harga terjangkau mulai dari **Rp 3.000** (Pensil Karakter). Cek produk termurah dan penawaran terbaik di [Katalog Produk](" . BASEURL . "/product).";
        }

        // 5. Fitur Ganti Password
        if (
            strpos($msg, 'ganti password') !== false ||
            strpos($msg, 'ubah password') !== false ||
            strpos($msg, 'lupa password') !== false ||
            strpos($msg, 'kata sandi') !== false ||
            strpos($msg, 'edit password') !== false ||
            strpos($msg, 'reset password') !== false
        ) {
            return "Langkah mengubah password akun:\n" .
                   "1. Buka menu [Pengaturan Akun](" . BASEURL . "/settings).\n" .
                   "2. Masukkan kata sandi saat ini dan kata sandi baru (minimal 6 karakter).\n" .
                   "3. Klik tombol **Ubah Password**.";
        }

        // 6. Pengaturan Tema Tampilan
        if (
            strpos($msg, 'tema') !== false ||
            strpos($msg, 'dark mode') !== false ||
            strpos($msg, 'mode gelap') !== false ||
            strpos($msg, 'mode terang') !== false ||
            strpos($msg, 'mode malam') !== false ||
            strpos($msg, 'tampilan') !== false ||
            strpos($msg, 'settings') !== false
        ) {
            return "Pengaturan tema tampilan nnaco_sa:\n" .
                   "- Mode Terang: Tampilan cerah pastel khas nnaco_sa.\n" .
                   "- Mode Gelap: Tampilan hitam obsidian untuk kenyamanan mata.\n" .
                   "- Pengaturan dapat diubah di menu [Pengaturan Tampilan](" . BASEURL . "/settings).";
        }

        // 7. Pertanyaan "siapa saya" / identitas pengguna sesuai aktor yang login
        if (
            strpos($msg, 'siapa saya') !== false ||
            strpos($msg, 'siapa aku') !== false ||
            strpos($msg, 'akun saya') !== false ||
            strpos($msg, 'role saya') !== false ||
            strpos($msg, 'status saya') !== false ||
            strpos($msg, 'identitas') !== false ||
            strpos($msg, 'profil saya') !== false
        ) {
            if ($userRole === 'owner') {
                return "Posisi akun Anda saat ini:\n" .
                       "- Nama: **{$currentUser['name']}**\n" .
                       "- Email: {$currentUser['email']}\n" .
                       "- Peran: **Owner (Pemilik Toko)**\n" .
                       "- Status Akun: **" . ucfirst($currentUser['status']) . "**";
            } elseif ($userRole === 'admin') {
                return "Posisi akun Anda saat ini:\n" .
                       "- Nama: **{$currentUser['name']}**\n" .
                       "- Email: {$currentUser['email']}\n" .
                       "- Peran: **Admin (Administrator Toko)**\n" .
                       "- Status Akun: **" . ucfirst($currentUser['status']) . "**";
            } elseif ($userRole === 'operator') {
                return "Posisi akun Anda saat ini:\n" .
                       "- Nama: **{$currentUser['name']}**\n" .
                       "- Email: {$currentUser['email']}\n" .
                       "- Peran: **Operator (Operator Pesanan Toko)**\n" .
                       "- Status Akun: **" . ucfirst($currentUser['status']) . "**";
            } elseif ($userRole === 'customer') {
                $res = "Posisi akun Anda saat ini:\n" .
                       "- Nama: **{$currentUser['name']}**\n" .
                       "- Email: {$currentUser['email']}\n" .
                       "- Peran: **Pelanggan (Customer)**\n" .
                       "- Status Akun: **" . ucfirst($currentUser['status']) . "**\n";
                if (!empty($userOrders)) {
                    $res .= "- Riwayat Pesanan: Total " . count($userOrders) . " pesanan ([Lihat Riwayat Belanja](" . BASEURL . "/CustomerController/orders))";
                } else {
                    $res .= "- Riwayat Pesanan: Belum ada pesanan belanja";
                }
                return $res;
            } else {
                return "Status Anda saat ini: **Tamu (Guest / Belum Login)**. Silakan [Login](" . BASEURL . "/auth/login) atau [Daftar Akun Baru](" . BASEURL . "/auth/register).";
            }
        }

        // 8. Proteksi & Jawaban Data Akun & Manajemen Pengguna (Disesuaikan dengan Aktor)
        $isUserRelatedQuery = (
            preg_match('/\b(user|users|pengguna|akun|member|role)\b/i', $msg) ||
            strpos($msg, 'bisa login') !== false ||
            strpos($msg, 'dapat login') !== false
        ) && (
            // Kecualikan jika menanyakan customer secara spesifik agar ditangani oleh step 9
            strpos($msg, 'customer') === false && strpos($msg, 'pelanggan') === false
        );

        if ($isUserRelatedQuery) {
            // 8.1. Panduan Tambah Pengguna Baru
            if (
                strpos($msg, 'tambah') !== false ||
                strpos($msg, 'buat') !== false ||
                strpos($msg, 'bikin') !== false ||
                strpos($msg, 'create') !== false ||
                strpos($msg, 'baru') !== false
            ) {
                if (in_array($userRole, ['owner', 'admin'])) {
                    return "Panduan menambah pengguna baru (Akses Admin/Owner):\n" .
                           "1. Masuk ke menu [Tambah Pengguna](" . BASEURL . "/AdminUserController/create).\n" .
                           "2. Lengkapi formulir: Nama Lengkap, Email, No. WhatsApp aktif, dan Kata Sandi.\n" .
                           "3. Tentukan Peran (Role: Admin, Operator, atau Customer) dan Status (Active).\n" .
                           "4. Klik tombol **Simpan Pengguna**.";
                } else {
                    return "Pendaftaran akun baru dapat dilakukan melalui halaman [Daftar Akun Baru](" . BASEURL . "/auth/register).";
                }
            }

            // 8.2. Panduan Kelola / Edit / Ubah / Hapus Pengguna
            if (
                strpos($msg, 'kelola') !== false ||
                strpos($msg, 'edit') !== false ||
                strpos($msg, 'ubah') !== false ||
                strpos($msg, 'ganti role') !== false ||
                strpos($msg, 'hapus') !== false ||
                strpos($msg, 'nonaktif') !== false
            ) {
                if (in_array($userRole, ['owner', 'admin'])) {
                    return "Panduan mengelola pengguna (Akses Admin/Owner):\n" .
                           "1. Buka menu [Manajemen Pengguna](" . BASEURL . "/AdminUserController).\n" .
                           "2. Cari akun yang dituju, lalu klik tombol **Edit**.\n" .
                           "3. Anda dapat memperbarui Nama, Email, No. WhatsApp, Peran (Role), dan Status Akun (Active/Inactive).\n" .
                           "4. Klik tombol **Perbarui Pengguna**.";
                } else {
                    return "Akses kelola akun pengguna dibatasi hanya untuk Admin dan Owner.";
                }
            }

            // 8.3. Daftar Seluruh Pengguna
            if (
                strpos($msg, 'berapa') === false &&
                strpos($msg, 'jumlah') === false &&
                strpos($msg, 'total') === false &&
                (
                    preg_match('/\bdaftar\b/i', $msg) ||
                    strpos($msg, 'list') !== false ||
                    strpos($msg, 'siapa saja') !== false ||
                    strpos($msg, 'semua') !== false ||
                    strpos($msg, 'ada siapa') !== false
                )
            ) {
                if (in_array($userRole, ['owner', 'admin'])) {
                    $res = "Daftar pengguna terdaftar di database nnaco_sa (" . count($allUsers) . " orang):\n";
                    $i = 1;
                    foreach ($allUsers as $u) {
                        $res .= "{$i}. **{$u['name']}** ({$u['email']}) — Role: **" . ucfirst($u['role']) . "**, Status: **" . ucfirst($u['status']) . "**\n";
                        $i++;
                    }
                    $res .= "Kelola seluruh akun di [Manajemen Pengguna](" . BASEURL . "/AdminUserController).";
                    return trim($res);
                } elseif ($userRole === 'operator') {
                    return "Akses data akun pengguna terbatas untuk Owner dan Admin.";
                } else {
                    return "Informasi mengenai data akun terdaftar bersifat rahasia internal dan hanya dapat diakses oleh Admin/Owner.";
                }
            }

            // 8.4. Ringkasan Pengguna Umum
            if (in_array($userRole, ['owner', 'admin'])) {
                $canLoginCustomer = $userStats['can_login_by_role']['customer'] ?? 0;
                $canLoginAdmin = $userStats['can_login_by_role']['admin'] ?? 0;
                $canLoginOperator = $userStats['can_login_by_role']['operator'] ?? 0;
                $canLoginOwner = $userStats['can_login_by_role']['owner'] ?? 0;

                return "Data akun pengguna di database nnaco_sa:\n" .
                       "- Total Pengguna: **{$userStats['total']} orang**\n" .
                       "- Akun Bisa Login: **{$userStats['can_login_total']} orang**\n" .
                       "- Akun Nonaktif: **{$userStats['cannot_login_total']} orang**\n" .
                       "- Rincian per Peran: Customer: **{$canLoginCustomer}**, Admin: **{$canLoginAdmin}**, Operator: **{$canLoginOperator}**, Owner: **{$canLoginOwner}**\n" .
                       "- Kelola di [Manajemen Pengguna](" . BASEURL . "/AdminUserController) • [Tambah Pengguna](" . BASEURL . "/AdminUserController/create)";
            } elseif ($userRole === 'operator') {
                return "Akses data akun pengguna terbatas untuk Owner dan Admin.";
            } else {
                return "Informasi mengenai data akun terdaftar bersifat rahasia internal dan hanya dapat diakses oleh Admin/Owner.";
            }
        }

        // 9. Pertanyaan Customer / Pelanggan Terdaftar
        $isCustomerListQuery = (
            preg_match('/^(?:customer|data customer|pelanggan|data pelanggan)[!,\.\?\s]*$/i', $msg) ||
            (
                (
                    strpos($msg, 'customer') !== false ||
                    strpos($msg, 'pelanggan') !== false ||
                    strpos($msg, 'konsumen') !== false ||
                    (strpos($msg, 'pembeli') !== false && strpos($msg, 'pesanan') === false && strpos($msg, 'order') === false)
                ) && (
                    strpos($msg, 'berapa') !== false ||
                    strpos($msg, 'daftar') !== false ||
                    strpos($msg, 'siapa') !== false ||
                    strpos($msg, 'ada apa') !== false ||
                    strpos($msg, 'semua') !== false ||
                    strpos($msg, 'list') !== false ||
                    strpos($msg, 'terdaftar') !== false ||
                    strpos($msg, 'apa saja') !== false ||
                    strpos($msg, 'data') !== false
                )
            )
        );

        if ($isCustomerListQuery) {
            if (in_array($userRole, ['owner', 'admin'])) {
                $customers = array_filter($allUsers, function($u) { return ($u['role'] ?? '') === 'customer'; });
                $res = "Daftar Customer terdaftar di database nnaco_sa (" . count($customers) . " orang):\n";
                $i = 1;
                foreach ($customers as $c) {
                    $email = !empty($c['email']) ? " ({$c['email']})" : "";
                    $res .= "{$i}. **{$c['name']}**{$email}\n";
                    $i++;
                }
                return trim($res);
            } elseif ($userRole === 'operator') {
                return "Akses data customer terbatas untuk Owner dan Admin.";
            } else {
                return "Informasi mengenai data akun terdaftar bersifat rahasia internal dan hanya dapat diakses oleh Admin/Owner.";
            }
        }

        // 10. Pertanyaan Staf Internal (Admin, Operator, Owner)
        if (strpos($msg, 'admin') !== false || strpos($msg, 'operator') !== false || strpos($msg, 'owner') !== false || strpos($msg, 'pemilik') !== false) {
            if (in_array($userRole, ['owner', 'admin', 'operator'])) {
                if (strpos($msg, 'admin') !== false) {
                    $admins = array_filter($allUsers, function($u) { return $u['role'] === 'admin'; });
                    $res = "Akun Admin di toko nnaco_sa (" . count($admins) . " orang):\n";
                    foreach ($admins as $a) {
                        $res .= "- **{$a['name']}** ({$a['email']})\n";
                    }
                    return trim($res);
                }

                if (strpos($msg, 'operator') !== false) {
                    $operators = array_filter($allUsers, function($u) { return $u['role'] === 'operator'; });
                    $res = "Akun Operator di toko nnaco_sa (" . count($operators) . " orang):\n";
                    foreach ($operators as $op) {
                        $res .= "- **{$op['name']}** ({$op['email']})\n";
                    }
                    return trim($res);
                }

                if (strpos($msg, 'owner') !== false || strpos($msg, 'pemilik') !== false) {
                    $owners = array_filter($allUsers, function($u) { return $u['role'] === 'owner'; });
                    $res = "Pemilik (Owner) toko nnaco_sa:\n";
                    foreach ($owners as $ow) {
                        $res .= "- **{$ow['name']}** ({$ow['email']})\n";
                    }
                    return trim($res);
                }
            } else {
                return "Informasi mengenai staf toko bersifat rahasia internal. Untuk bantuan, silakan hubungi [WhatsApp Customer Care]({$waLink}).";
            }
        }

        // 11. Pertanyaan Isi Database Teknis / Struktur Tabel
        $isTechnicalDbQuery = (
            strpos($msg, 'struktur database') !== false ||
            strpos($msg, 'tabel database') !== false ||
            strpos($msg, 'isi tabel') !== false ||
            strpos($msg, 'schema') !== false ||
            strpos($msg, 'skema') !== false ||
            strpos($msg, 'daftar tabel') !== false ||
            (strpos($msg, 'tabel') !== false && strpos($msg, 'apa saja') !== false) ||
            ((strpos($msg, 'database') !== false || strpos($msg, 'tabel') !== false) &&
             strpos($msg, 'produk') === false &&
             strpos($msg, 'kategori') === false &&
             strpos($msg, 'harga') === false &&
             strpos($msg, 'stok') === false &&
             strpos($msg, 'barang') === false &&
             strpos($msg, 'pesanan') === false &&
             strpos($msg, 'order') === false &&
             strpos($msg, 'user') === false &&
             strpos($msg, 'pengguna') === false &&
             strpos($msg, 'akun') === false)
        );
        if ($isTechnicalDbQuery) {
            if (in_array($userRole, ['owner', 'admin'])) {
                return "Struktur data utama database nnaco_sa:\n" .
                       "1. Data Pengguna (Users): **{$userStats['total']} akun aktif**\n" .
                       "2. Data Kategori (Categories): **{$totalCategories} kategori**\n" .
                       "3. Data Produk (Products): **{$totalProducts} produk aktif**\n" .
                       "4. Data Pesanan (Orders): **{$totalOrders} transaksi pesanan**";
            } else {
                return "Informasi teknis database toko bersifat rahasia dan hanya dapat diakses oleh Admin/Owner.";
            }
        }

        // 12. Pertanyaan Omset / Laporan Keuangan & Penjualan
        $isLaporanQuery = (
            strpos($msg, 'laporan') !== false ||
            strpos($msg, 'rekap') !== false ||
            strpos($msg, 'keuangan') !== false ||
            (strpos($msg, 'penjualan') !== false && strpos($msg, 'cara') === false)
        );
        $isOmsetQuery = (
            strpos($msg, 'omset') !== false ||
            strpos($msg, 'pendapatan') !== false ||
            strpos($msg, 'revenue') !== false ||
            strpos($msg, 'keuntungan') !== false
        );

        if ($isOmsetQuery || $isLaporanQuery) {
            if (in_array($userRole, ['owner', 'admin'])) {
                if ($isLaporanQuery && !$isOmsetQuery) {
                    return "Laporan Penjualan & Keuangan nnaco_sa:\n" .
                           "- Total Omset Penjualan: **Rp " . number_format($totalRevenue, 0, ',', '.') . "**\n" .
                           "- Total Transaksi: **{$totalOrders} pesanan**\n" .
                           "- Rincian Status Pesanan: Pending: " . ($orderStats['total_pending'] ?? 0) . ", Processing: " . ($orderStats['total_processing'] ?? 0) . ", Shipped: " . ($orderStats['total_shipped'] ?? 0) . ", Completed: " . ($orderStats['total_completed'] ?? 0) . "\n" .
                           "- Pantau di [" . ($userRole === 'owner' ? "Dashboard Owner" : "Dashboard Admin") . "](" . BASEURL . "/" . ($userRole === 'owner' ? "owner" : "admin") . ") • [Manajemen Pesanan](" . BASEURL . "/AdminOrderController)";
                } else {
                    return "Total omset transaksi toko nnaco_sa saat ini adalah **Rp " . number_format($totalRevenue, 0, ',', '.') . "** dari total **{$totalOrders} pesanan**.";
                }
            } elseif ($userRole === 'operator') {
                return "Akses laporan keuangan dan omset toko hanya dapat diakses oleh Owner dan Admin.";
            } else {
                return "Informasi mengenai omset dan laporan keuangan toko bersifat rahasia internal pengelola toko.";
            }
        }

        // 13. Pelacakan Pesanan / Transaksi / Order Sesuai Aktor
        $isOrderQuery = (
            strpos($msg, 'pesanan') !== false ||
            strpos($msg, 'order') !== false ||
            strpos($msg, 'transaksi') !== false ||
            strpos($msg, 'lacak') !== false ||
            (strpos($msg, 'siapa') !== false && (strpos($msg, 'beli') !== false || strpos($msg, 'pesan') !== false || strpos($msg, 'order') !== false)) ||
            (strpos($msg, 'daftar') !== false && (strpos($msg, 'beli') !== false || strpos($msg, 'pemesan') !== false)) ||
            strpos($msg, 'pemesan') !== false ||
            strpos($msg, 'pembeli') !== false
        );

        if ($isOrderQuery) {
            // Cek apakah ada nomor order spesifik (misal ORD-...)
            if (preg_match('/(ord-[\w\-]+)/i', $msg, $matchOrder)) {
                $searchedOrderNumber = strtoupper($matchOrder[1]);
                $foundOrder = $orderModel->getOrderByNumber($searchedOrderNumber);

                if ($foundOrder) {
                    // Verifikasi hak akses: Owner, Admin, Operator, atau Pemilik pesanan itu sendiri
                    if (in_array($userRole, ['owner', 'admin', 'operator']) || ($currentUser && $currentUser['id'] == $foundOrder['user_id'])) {
                        $orderItems = $orderModel->getOrderItems($foundOrder['id']);
                        $res = "Detail Pesanan #{$foundOrder['order_number']}:\n" .
                               "- Status Pesanan: **" . ucfirst($foundOrder['status']) . "**\n" .
                               "- Status Pembayaran: **" . ucfirst($foundOrder['payment_status']) . "**\n" .
                               "- Penerima: {$foundOrder['shipping_name']} ({$foundOrder['shipping_phone']})\n" .
                               "- Alamat: {$foundOrder['shipping_address']}\n" .
                               "- Total: **Rp " . number_format($foundOrder['total_amount'], 0, ',', '.') . "**\n";
                        if (!empty($foundOrder['tracking_number'])) {
                            $res .= "- Kurir/Resi: {$foundOrder['shipping_courier']} ({$foundOrder['tracking_number']})\n";
                        }
                        if (!empty($orderItems)) {
                            $res .= "Daftar Barang:\n";
                            foreach ($orderItems as $item) {
                                $res .= "* {$item['product_name']} ({$item['quantity']}x) — Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
                            }
                        }
                        return trim($res);
                    } else {
                        return "Anda tidak memiliki akses untuk melihat rincian pesanan tersebut.";
                    }
                } else {
                    return "Nomor pesanan {$searchedOrderNumber} tidak ditemukan di database nnaco_sa.";
                }
            }

            if (in_array($userRole, ['owner', 'admin', 'operator'])) {
                // 13.1. Cek jika menanyakan pesanan pending / belum diproses
                if (strpos($msg, 'pending') !== false || strpos($msg, 'menunggu') !== false || strpos($msg, 'belum diproses') !== false || strpos($msg, 'belum konfirmasi') !== false) {
                    $pendingOrders = array_filter($allOrders, function($o) {
                        return strtolower($o['status']) === 'pending';
                    });
                    if (!empty($pendingOrders)) {
                        $res = "Daftar pesanan dengan status **Pending** (" . count($pendingOrders) . " pesanan):\n";
                        $i = 1;
                        foreach ($pendingOrders as $po) {
                            $res .= "{$i}. **#{$po['order_number']}** — **{$po['shipping_name']}** | Rp " . number_format($po['total_amount'], 0, ',', '.') . " | [Detail Pesanan](" . BASEURL . "/adminOrder/detail/{$po['id']})\n";
                            $i++;
                        }
                        return trim($res);
                    } else {
                        return "Saat ini tidak ada pesanan dengan status pending di database nnaco_sa.";
                    }
                }

                // 13.2. Cek jika menanyakan pesanan confirmed / dikonfirmasi
                if (strpos($msg, 'confirmed') !== false || strpos($msg, 'dikonfirmasi') !== false) {
                    $confirmedOrders = array_filter($allOrders, function($o) {
                        return strtolower($o['status']) === 'confirmed';
                    });
                    if (!empty($confirmedOrders)) {
                        $res = "Daftar pesanan dengan status **Confirmed** (" . count($confirmedOrders) . " pesanan):\n";
                        $i = 1;
                        foreach ($confirmedOrders as $co) {
                            $res .= "{$i}. **#{$co['order_number']}** — **{$co['shipping_name']}** | Rp " . number_format($co['total_amount'], 0, ',', '.') . " | [Detail Pesanan](" . BASEURL . "/adminOrder/detail/{$co['id']})\n";
                            $i++;
                        }
                        return trim($res);
                    } else {
                        return "Saat ini tidak ada pesanan dengan status confirmed di database nnaco_sa.";
                    }
                }

                // 13.3. Cek jika menanyakan pesanan selesai / completed
                if (strpos($msg, 'completed') !== false || strpos($msg, 'selesai') !== false) {
                    $completedOrders = array_filter($allOrders, function($o) {
                        return strtolower($o['status']) === 'completed';
                    });
                    if (!empty($completedOrders)) {
                        $res = "Daftar pesanan dengan status **Completed** (" . count($completedOrders) . " pesanan):\n";
                        $i = 1;
                        foreach ($completedOrders as $cmo) {
                            $res .= "{$i}. **#{$cmo['order_number']}** — **{$cmo['shipping_name']}** | Rp " . number_format($cmo['total_amount'], 0, ',', '.') . " | [Detail Pesanan](" . BASEURL . "/adminOrder/detail/{$cmo['id']})\n";
                            $i++;
                        }
                        return trim($res);
                    } else {
                        return "Saat ini tidak ada pesanan dengan status completed di database nnaco_sa.";
                    }
                }

                // 13.4. Cek jika menanyakan siapa yang pesan / siapa yang beli / pemesan
                if (
                    (strpos($msg, 'siapa') !== false && (strpos($msg, 'pesan') !== false || strpos($msg, 'beli') !== false || strpos($msg, 'order') !== false)) ||
                    strpos($msg, 'pemesan') !== false ||
                    strpos($msg, 'pembeli') !== false
                ) {
                    if (!empty($allOrders)) {
                        $res = "Daftar pemesan dari transaksi pesanan toko:\n";
                        $i = 1;
                        foreach (array_slice($allOrders, 0, 6) as $ao) {
                            $res .= "{$i}. **{$ao['shipping_name']}** — Order **#{$ao['order_number']}** (Rp " . number_format($ao['total_amount'], 0, ',', '.') . ", Status: " . ucfirst($ao['status']) . ") | [Detail](" . BASEURL . "/adminOrder/detail/{$ao['id']})\n";
                            $i++;
                        }
                        return trim($res);
                    } else {
                        return "Belum ada data pemesan di database nnaco_sa.";
                    }
                }

                // 13.5. Daftar transaksi pesanan umum
                if (!empty($allOrders)) {
                    if ($userRole === 'operator') {
                        $res = "Operasional pesanan masuk (Akses Operator):\n" .
                               "- Total Pesanan: **{$totalOrders} pesanan** (Pending: " . ($orderStats['total_pending'] ?? 0) . ", Processing: " . ($orderStats['total_processing'] ?? 0) . ", Shipped: " . ($orderStats['total_shipped'] ?? 0) . ", Completed: " . ($orderStats['total_completed'] ?? 0) . ")\n" .
                               "Daftar Transaksi Terbaru:\n";
                        $i = 1;
                        foreach (array_slice($allOrders, 0, 5) as $ao) {
                            $res .= "{$i}. **#{$ao['order_number']}** — **{$ao['shipping_name']}** | Rp " . number_format($ao['total_amount'], 0, ',', '.') . " | Status: **" . ucfirst($ao['status']) . "** | [Detail](" . BASEURL . "/adminOrder/detail/{$ao['id']})\n";
                            $i++;
                        }
                        $res .= "- Kelola pesanan di [Dashboard Operator](" . BASEURL . "/operator)";
                        return trim($res);
                    } else {
                        $res = "Daftar transaksi pesanan di database nnaco_sa (" . count($allOrders) . " pesanan):\n";
                        $i = 1;
                        foreach (array_slice($allOrders, 0, 6) as $ao) {
                            $res .= "{$i}. **#{$ao['order_number']}** — **{$ao['shipping_name']}** | Rp " . number_format($ao['total_amount'], 0, ',', '.') . " | Status: **" . ucfirst($ao['status']) . "** | [Detail](" . BASEURL . "/adminOrder/detail/{$ao['id']})\n";
                            $i++;
                        }
                        $res .= "- Total Omset: **Rp " . number_format($totalRevenue, 0, ',', '.') . "**\n";
                        $res .= "- Kelola di [Manajemen Pesanan](" . BASEURL . "/AdminOrderController)";
                        return trim($res);
                    }
                } else {
                    return "Belum ada transaksi pesanan di database nnaco_sa.";
                }
            } elseif ($userRole === 'customer') {
                if (!empty($userOrders)) {
                    $res = "Riwayat pesanan Anda (" . count($userOrders) . " pesanan):\n";
                    $i = 1;
                    foreach (array_slice($userOrders, 0, 5) as $o) {
                        $res .= "{$i}. Order **#{$o['order_number']}** | Status: **" . ucfirst($o['status']) . "** | Total: **Rp " . number_format($o['total_amount'], 0, ',', '.') . "** | [Detail](" . BASEURL . "/CustomerController/orderDetail/{$o['id']})\n";
                        $i++;
                    }
                    return trim($res);
                } else {
                    return "Anda belum memiliki riwayat pesanan belanja di toko nnaco_sa.";
                }
            } else {
                return "Untuk mengecek status pesanan belanja, silakan [Login](" . BASEURL . "/auth/login) ke akun Anda terlebih dahulu.";
            }
        }

        // 13.6. Panduan Menambah / Mengelola Produk & Kategori (Admin/Owner)
        $isProductManageGuide = (
            (strpos($msg, 'produk') !== false || strpos($msg, 'barang') !== false) &&
            (strpos($msg, 'tambah') !== false || strpos($msg, 'buat') !== false || strpos($msg, 'input') !== false || strpos($msg, 'kelola') !== false || strpos($msg, 'edit') !== false || strpos($msg, 'hapus') !== false)
        );
        $isCategoryManageGuide = (
            strpos($msg, 'kategori') !== false &&
            (strpos($msg, 'tambah') !== false || strpos($msg, 'buat') !== false || strpos($msg, 'input') !== false || strpos($msg, 'kelola') !== false)
        );

        if ($isProductManageGuide) {
            if (in_array($userRole, ['owner', 'admin'])) {
                return "Panduan menambah/mengelola produk (Akses Admin):\n" .
                       "1. Buka menu [Manajemen Produk](" . BASEURL . "/AdminProductController).\n" .
                       "2. Untuk menambah produk baru, klik tombol [Tambah Produk](" . BASEURL . "/AdminProductController/create).\n" .
                       "3. Masukkan Nama Produk, Kategori, Harga (Rp), Stok Unit, Deskripsi, dan Foto Produk.\n" .
                       "4. Klik tombol **Simpan Produk**.";
            } else {
                return "Pengelolaan produk hanya dapat dilakukan oleh Admin toko.";
            }
        }

        if ($isCategoryManageGuide) {
            if (in_array($userRole, ['owner', 'admin'])) {
                return "Panduan menambah/mengelola kategori produk (Akses Admin):\n" .
                       "1. Buka menu [Kategori Produk](" . BASEURL . "/AdminProductController).\n" .
                       "2. Masukkan Nama Kategori baru dan simpan.";
            } else {
                return "Pengelolaan kategori hanya dapat dilakukan oleh Admin toko.";
            }
        }

        // 14. Pertanyaan Produk Termurah
        if (strpos($msg, 'termurah') !== false || (strpos($msg, 'murah') !== false && (strpos($msg, 'apa') !== false || strpos($msg, 'paling') !== false || strpos($msg, 'rekomendasi') !== false))) {
            $sorted = $activeProducts;
            usort($sorted, function($a, $b) { return $a['price'] <=> $b['price']; });
            $topCheapest = array_slice($sorted, 0, 4);

            $res = "Daftar produk termurah di katalog nnaco_sa:\n";
            $i = 1;
            foreach ($topCheapest as $cp) {
                $res .= "{$i}. **{$cp['name']}** — **Rp " . number_format($cp['price'], 0, ',', '.') . "** | [Lihat Produk](" . BASEURL . "/product/detail/{$cp['slug']})\n";
                $i++;
            }
            return trim($res);
        }

        // 15. Pertanyaan Produk Termahal & Paling Eksklusif
        if (strpos($msg, 'termahal') !== false || (strpos($msg, 'mahal') !== false && (strpos($msg, 'apa') !== false || strpos($msg, 'paling') !== false))) {
            $sorted = $activeProducts;
            usort($sorted, function($a, $b) { return $b['price'] <=> $a['price']; });
            $topExpensive = array_slice($sorted, 0, 3);

            $res = "Daftar produk termahal di toko nnaco_sa:\n";
            $i = 1;
            foreach ($topExpensive as $ep) {
                $res .= "{$i}. **{$ep['name']}** — **Rp " . number_format($ep['price'], 0, ',', '.') . "** (Kategori: {$ep['category_name']}) | [Lihat Produk](" . BASEURL . "/product/detail/{$ep['slug']})\n";
                $i++;
            }
            return trim($res);
        }

        // 16. Filter Harga Berdasarkan Budget (Misal: "di bawah 50 ribu", "di bawah 30rb", "budget 20000")
        if (
            strpos($msg, 'di bawah') !== false ||
            strpos($msg, 'dibawah') !== false ||
            strpos($msg, 'kurang dari') !== false ||
            strpos($msg, 'maksimal') !== false ||
            strpos($msg, 'budget') !== false
        ) {
            $budgetLimit = 0;
            if (preg_match('/(\d+)\s*(ribu|rb|k)/i', $msg, $m)) {
                $budgetLimit = (int)$m[1] * 1000;
            } elseif (preg_match('/(\d{1,3}(?:\.\d{3})+)/', $msg, $m)) {
                $budgetLimit = (int)str_replace('.', '', $m[1]);
            } elseif (preg_match('/(\d{4,9})/', $msg, $m)) {
                $budgetLimit = (int)$m[1];
            }

            if ($budgetLimit > 0) {
                $budgetProducts = array_filter($activeProducts, function($p) use ($budgetLimit) {
                    return (float)$p['price'] <= $budgetLimit;
                });

                if (!empty($budgetProducts)) {
                    usort($budgetProducts, function($a, $b) { return $a['price'] <=> $b['price']; });
                    $budgetProducts = array_slice($budgetProducts, 0, 5);

                    $res = "Daftar produk dengan harga di bawah Rp " . number_format($budgetLimit, 0, ',', '.') . ":\n";
                    $i = 1;
                    foreach ($budgetProducts as $bp) {
                        $stockInfo = $bp['stock'] > 0 ? "Stok: {$bp['stock']}" : "Habis";
                        $res .= "{$i}. **{$bp['name']}** — **Rp " . number_format($bp['price'], 0, ',', '.') . "** ({$stockInfo}) | [Lihat Produk](" . BASEURL . "/product/detail/{$bp['slug']})\n";
                        $i++;
                    }
                    return trim($res);
                } else {
                    return "Tidak ada produk dengan harga di bawah Rp " . number_format($budgetLimit, 0, ',', '.') . ". Produk termurah mulai dari Rp " . number_format($minPrice, 0, ',', '.') . ".";
                }
            }
        }

        // 17. Rekomendasi Kado / Hadiah / Populer
        if (
            strpos($msg, 'rekomendasi') !== false ||
            strpos($msg, 'kado') !== false ||
            strpos($msg, 'hadiah') !== false ||
            strpos($msg, 'ulang tahun') !== false ||
            strpos($msg, 'pacar') !== false ||
            strpos($msg, 'sahabat') !== false ||
            strpos($msg, 'spesial') !== false ||
            strpos($msg, 'populer') !== false
        ) {
            $recommendedSlugs = [
                'kotak-hadiah-handmade',
                'kalung-mutiara',
                'gelang-handmade',
                'gantungan-kunci-akrilik-custom',
                'hiasan-ulang-tahun-eksklusif',
                'bando-eksklusif'
            ];
            $giftProducts = array_filter($activeProducts, function($p) use ($recommendedSlugs) {
                return in_array($p['slug'], $recommendedSlugs);
            });
            if (empty($giftProducts)) {
                $giftProducts = array_slice($activeProducts, 0, 4);
            }

            $res = "Rekomendasi aksesoris untuk kado/hadiah:\n";
            $i = 1;
            foreach ($giftProducts as $gp) {
                $res .= "{$i}. **{$gp['name']}** — **Rp " . number_format($gp['price'], 0, ',', '.') . "** (Kategori: {$gp['category_name']}) | [Lihat Produk](" . BASEURL . "/product/detail/{$gp['slug']})\n";
                $i++;
            }
            return trim($res);
        }

        // 17.5. Pertanyaan Daftar Semua Produk / Katalog Produk Toko
        $isListAllProducts = (
            strpos($msg, 'berapa') === false &&
            strpos($msg, 'jumlah') === false &&
            strpos($msg, 'total') === false &&
            (
                preg_match('/^(?:produk|data produk|barang|data barang|katalog)[!,\.\?\s]*$/i', $msg) ||
                strpos($msg, 'ada produk apa saja') !== false ||
                strpos($msg, 'produk apa saja') !== false ||
                strpos($msg, 'daftar produk') !== false ||
                strpos($msg, 'semua produk') !== false ||
                strpos($msg, 'katalog produk') !== false ||
                strpos($msg, 'sebutkan produk') !== false ||
                strpos($msg, 'sebutkan semua produk') !== false ||
                strpos($msg, 'list produk') !== false ||
                strpos($msg, 'ada barang apa saja') !== false ||
                strpos($msg, 'barang apa saja') !== false ||
                strpos($msg, 'daftar barang') !== false ||
                strpos($msg, 'semua barang') !== false ||
                strpos($msg, 'isi produk') !== false ||
                (strpos($msg, 'produk') !== false && (strpos($msg, 'database') !== false || strpos($msg, 'toko') !== false) && (strpos($msg, 'daftar') !== false || strpos($msg, 'sebutkan') !== false || preg_match('/\b(apa|ada)\b/', $msg)))
            )
        );

        if ($isListAllProducts) {
            $res = "Daftar produk aktif di database nnaco_sa ({$totalProducts} produk):\n";
            $i = 1;
            foreach (array_slice($activeProducts, 0, 10) as $p) {
                $stock = $p['stock'] > 0 ? "Stok: {$p['stock']} unit" : "Habis";
                $res .= "{$i}. **{$p['name']}** — **Rp " . number_format($p['price'], 0, ',', '.') . "** ({$stock}) | [Lihat](" . BASEURL . "/product/detail/{$p['slug']})\n";
                $i++;
            }
            if ($totalProducts > 10) {
                $res .= "... dan " . ($totalProducts - 10) . " produk lainnya.\n";
            }
            if (in_array($userRole, ['owner', 'admin'])) {
                $res .= "Kelola di [Manajemen Produk](" . BASEURL . "/AdminProductController) • [Tambah Produk](" . BASEURL . "/AdminProductController/create)";
            } else {
                $res .= "Lihat katalog lengkap di [Katalog Produk](" . BASEURL . "/product).";
            }
            return trim($res);
        }

        // 18. Pencarian Cerdas Produk dari Database (Tunggal & Multi-Produk)
        $stopWords = ['apakah', 'berapa', 'harganya', 'harga', 'stok', 'stock', 'ready', 'sisa', 'ada', 'tidak', 'gak', 'ngga', 'bisa', 'beli', 'mau', 'jual', 'produk', 'toko', 'ini', 'itu', 'ya', 'saja', 'koleksi', 'katalog', 'rp', 'unit', 'yang', 'dan', 'atau', 'apa', 'sih', 'dong', 'kah', 'gimana', 'kak'];
        
        $msgWords = array_values(array_filter(preg_split('/[\s,\.\?\!\-]+/', $msg), function($w) use ($stopWords) {
            return strlen($w) >= 3 && !in_array($w, $stopWords);
        }));

        $matchedProducts = [];
        foreach ($activeProducts as $p) {
            $pNameLower = strtolower($p['name']);
            
            // 1. Cek kecocokan nama lengkap langsung
            if (strpos($msg, $pNameLower) !== false) {
                $matchedProducts[] = ['product' => $p, 'score' => 100];
                continue;
            }

            // 2. Cek kecocokan kata-kata inti dalam nama produk (abaikan 'produk', 'eksklusif')
            $pWords = array_values(array_filter(preg_split('/[\s,\.\?\!\-]+/', $pNameLower), function($w) {
                return strlen($w) >= 3 && !in_array($w, ['produk', 'eksklusif']);
            }));

            $score = 0;
            foreach ($msgWords as $mw) {
                foreach ($pWords as $pw) {
                    if ($mw === $pw) {
                        $score += 30;
                    } elseif (strlen($mw) >= 4 && strlen($pw) >= 4 && (strpos($pw, $mw) === 0 || strpos($mw, $pw) === 0)) {
                        $score += 20;
                    }
                }
            }

            if ($score > 0) {
                $matchedProducts[] = ['product' => $p, 'score' => $score];
            }
        }

        if (!empty($matchedProducts)) {
            usort($matchedProducts, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $topScore = $matchedProducts[0]['score'];
            $topMatches = array_values(array_filter($matchedProducts, function($m) use ($topScore) {
                return $m['score'] >= ($topScore * 0.7);
            }));

            $isAskingPrice = (
                strpos($msg, 'harga') !== false ||
                strpos($msg, 'harganya') !== false ||
                strpos($msg, 'berapa rp') !== false ||
                strpos($msg, 'rp') !== false ||
                (strpos($msg, 'berapa') !== false && strpos($msg, 'stok') === false && strpos($msg, 'sisa') === false)
            );

            $isAskingStock = (
                strpos($msg, 'stok') !== false ||
                strpos($msg, 'stock') !== false ||
                strpos($msg, 'ready') !== false ||
                strpos($msg, 'tersedia') !== false ||
                strpos($msg, 'ketersediaan') !== false ||
                strpos($msg, 'sisa') !== false ||
                strpos($msg, 'ada gak') !== false ||
                strpos($msg, 'ada ngga') !== false ||
                strpos($msg, 'ada tidak') !== false ||
                strpos($msg, 'apakah ada') !== false ||
                strpos($msg, 'masih ada') !== false
            );

            // Jika hanya 1 produk yang sangat cocok
            if (count($topMatches) === 1 || ($topScore >= 50 && ($topMatches[0]['score'] - ($topMatches[1]['score'] ?? 0)) >= 30)) {
                $p = $topMatches[0]['product'];
                $stockInfo = $p['stock'] > 0 ? "Tersedia ({$p['stock']} unit)" : "Stok Habis (0 unit)";
                $productUrl = BASEURL . "/product/detail/{$p['slug']}";
                $formattedPrice = "Rp " . number_format($p['price'], 0, ',', '.');

                if ($isAskingPrice && !$isAskingStock) {
                    return "Harga **{$p['name']}**: **{$formattedPrice}** (Stok: {$p['stock']} unit). [Lihat Produk]({$productUrl})";
                } elseif ($isAskingStock && !$isAskingPrice) {
                    return "Stok **{$p['name']}**: **{$p['stock']} unit** ({$stockInfo}). Harga: {$formattedPrice}. [Lihat Produk]({$productUrl})";
                } else {
                    return "**{$p['name']}** — **{$formattedPrice}** (Kategori: {$p['category_name']}, Stok: {$p['stock']} unit). [Lihat Produk]({$productUrl})";
                }
            } else {
                // Cek apakah pertanyaan merujuk pada kategori spesifik (misal: "ada gelang apa saja?")
                foreach ($activeCategories as $cat) {
                    $catLower = strtolower($cat['name']);
                    if (strpos($msg, $catLower) !== false) {
                        $catProducts = array_filter($activeProducts, function($cp) use ($cat) {
                            return $cp['category_id'] == $cat['id'];
                        });
                        if (!empty($catProducts)) {
                            $res = "Daftar produk kategori **{$cat['name']}**:\n";
                            $ci = 1;
                            foreach (array_slice($catProducts, 0, 5) as $cp) {
                                $stk = $cp['stock'] > 0 ? "Stok: {$cp['stock']}" : "Habis";
                                $res .= "{$ci}. **{$cp['name']}** — **Rp " . number_format($cp['price'], 0, ',', '.') . "** ({$stk}) | [Lihat Produk](" . BASEURL . "/product/detail/{$cp['slug']})\n";
                                $ci++;
                            }
                            return trim($res);
                        }
                    }
                }

                // Multi-produk cocok
                $res = "Data produk ditemukan di katalog nnaco_sa:\n";
                foreach (array_slice($topMatches, 0, 5) as $item) {
                    $mp = $item['product'];
                    $stockStatus = $mp['stock'] > 0 ? "Tersedia ({$mp['stock']} unit)" : "Stok Habis";
                    $res .= "- **{$mp['name']}** | Rp " . number_format($mp['price'], 0, ',', '.') . " | Kategori: {$mp['category_name']} | {$stockStatus} | [Lihat Produk](" . BASEURL . "/product/detail/{$mp['slug']})\n";
                }
                return trim($res);
            }
        }

        // 19. Pencarian Berdasarkan Kategori Spesifik (misal: "ada gelang apa saja?", "apakah ada kalung?", "produk bando")
        foreach ($activeCategories as $cat) {
            $catLower = strtolower($cat['name']);
            if (strpos($msg, $catLower) !== false) {
                $catProducts = array_filter($activeProducts, function($p) use ($cat) {
                    return $p['category_id'] == $cat['id'];
                });

                if (!empty($catProducts)) {
                    $catProducts = array_slice($catProducts, 0, 5);
                    $res = "Daftar produk kategori **{$cat['name']}**:\n";
                    $i = 1;
                    foreach ($catProducts as $cp) {
                        $stockInfo = $cp['stock'] > 0 ? "Stok: {$cp['stock']}" : "Habis";
                        $res .= "{$i}. **{$cp['name']}** — **Rp " . number_format($cp['price'], 0, ',', '.') . "** ({$stockInfo}) | [Lihat Produk](" . BASEURL . "/product/detail/{$cp['slug']})\n";
                        $i++;
                    }
                    return trim($res);
                }
            }
        }

        // 20. Pertanyaan Total / Daftar Kategori Produk
        if (
            preg_match('/^(?:kategori|data kategori|list kategori)[!,\.\?\s]*$/i', $msg) ||
            strpos($msg, 'kategori apa') !== false ||
            strpos($msg, 'daftar kategori') !== false ||
            strpos($msg, 'ada kategori apa') !== false ||
            strpos($msg, 'semua kategori') !== false ||
            strpos($msg, 'total kategori') !== false ||
            strpos($msg, 'berapa kategori') !== false ||
            (strpos($msg, 'kategori') !== false && (strpos($msg, 'semua') !== false || strpos($msg, 'apa saja') !== false || strpos($msg, 'berapa') !== false || strpos($msg, 'ada apa') !== false))
        ) {
            $catList = [];
            foreach ($activeCategories as $c) {
                $catList[] = "[{$c['name']}](" . BASEURL . "/product?category={$c['slug']})";
            }
            $res = "Total **{$totalCategories} kategori produk** aktif di nnaco_sa:\n" .
                   implode(' • ', $catList);
            if (in_array($userRole, ['owner', 'admin'])) {
                $res .= "\nKelola di [Kategori Produk](" . BASEURL . "/AdminProductController)";
            }
            return $res;
        }

        // 21. Pencarian Multi-Produk Berdasarkan Kata Kunci Umum
        $multiMatched = [];
        $queryWords = array_values(array_filter(preg_split('/\s+/', $msg), function($w) {
            return strlen($w) >= 3 && !in_array($w, ['apa', 'ada', 'yang', 'dan', 'atau', 'ini', 'itu', 'bisa', 'mau', 'beli', 'toko', 'produk', 'berapa']);
        }));
        if (!empty($queryWords)) {
            foreach ($activeProducts as $p) {
                $pName = strtolower($p['name']);
                foreach ($queryWords as $qw) {
                    if (strpos($pName, $qw) !== false) {
                        $multiMatched[] = $p;
                        break;
                    }
                }
            }
        }
        if (!empty($multiMatched)) {
            $multiMatched = array_slice($multiMatched, 0, 4);
            $res = "Data produk ditemukan di katalog nnaco_sa:\n";
            foreach ($multiMatched as $mp) {
                $stockStatus = $mp['stock'] > 0 ? "Tersedia ({$mp['stock']} unit)" : "Stok Habis";
                $res .= "- **{$mp['name']}** | Rp " . number_format($mp['price'], 0, ',', '.') . " | Kategori: {$mp['category_name']} | {$stockStatus} | [Lihat Produk](" . BASEURL . "/product/detail/{$mp['slug']})\n";
            }
            return trim($res);
        }

        // 22. Pertanyaan Ketersediaan Stok Toko
        if (strpos($msg, 'stok') !== false || strpos($msg, 'ready') !== false || strpos($msg, 'ketersediaan') !== false) {
            $inStockProducts = array_filter($activeProducts, function($p) { return $p['stock'] > 0; });
            return "Ketersediaan produk di database nnaco_sa: **" . count($inStockProducts) . " produk ready stock** dari total {$totalProducts} produk aktif.";
        }

        // 23. Pertanyaan Jumlah / Total Produk di Database
        if (strpos($msg, 'produk') !== false && (strpos($msg, 'berapa') !== false || strpos($msg, 'jumlah') !== false || strpos($msg, 'total') !== false || strpos($msg, 'ada apa') !== false || strpos($msg, 'banyak') !== false)) {
            return "Total produk aktif di database nnaco_sa: **{$totalProducts} produk** dalam **{$totalCategories} kategori** (rentang harga Rp " . number_format($minPrice, 0, ',', '.') . " s/d Rp " . number_format($maxPrice, 0, ',', '.') . "). [Katalog Produk](" . BASEURL . "/product)";
        }

        // 23.1. Pertanyaan Profil Toko / Jual Apa Saja / Apa yang Dijual
        if (
            strpos($msg, 'jual apa') !== false ||
            strpos($msg, 'menjual apa') !== false ||
            strpos($msg, 'profil toko') !== false ||
            strpos($msg, 'tentang toko') !== false ||
            strpos($msg, 'apa itu nnaco') !== false ||
            strpos($msg, 'koleksi apa') !== false ||
            (strpos($msg, 'toko') !== false && strpos($msg, 'jual') !== false) ||
            (strpos($msg, 'produk') !== false && (strpos($msg, 'apa itu') !== false || strpos($msg, 'jenis produk') !== false))
        ) {
            $catSummary = array_slice($categoryNames, 0, 8);
            return "Toko **nnaco_sa** adalah toko online aksesoris handmade, fashion, dan perhiasan cantik.\n" .
                   "Tersedia **{$totalProducts} produk aktif** dalam **{$totalCategories} kategori** (" . implode(', ', $catSummary) . ", dan lainnya).\n" .
                   "Lihat seluruh koleksi lengkap di [Katalog Produk](" . BASEURL . "/product).";
        }

        // 24. Default Sesuai Posisi Aktor (Langsung ke Fakta, Tanpa Komentar)
        if ($returnNullOnNoMatch) {
            return null;
        }

        return $this->getActorDefaultSummary($userRole, $totalProducts, $totalCategories, $userStats, $totalOrders, $totalRevenue, $orderStats, $minPrice, $maxPrice);
    }

    /**
     * Ringkasan operasional default berdasarkan posisi aktor
     */
    private function getActorDefaultSummary($userRole, $totalProducts, $totalCategories, $userStats, $totalOrders, $totalRevenue, $orderStats, $minPrice, $maxPrice) {
        if ($userRole === 'owner') {
            return "Data Operasional nnaco_sa (Akses Owner):\n" .
                   "- Produk Aktif: **{$totalProducts} produk** ({$totalCategories} kategori)\n" .
                   "- Pengguna Terdaftar: **{$userStats['total']} orang** (Bisa login: {$userStats['can_login_total']})\n" .
                   "- Transaksi Pesanan: **{$totalOrders} pesanan** (Total Omset: **Rp " . number_format($totalRevenue, 0, ',', '.') . "**)\n" .
                   "- Dashboard: [Dashboard Owner](" . BASEURL . "/owner)";
        } elseif ($userRole === 'admin') {
            return "Data Operasional nnaco_sa (Akses Admin):\n" .
                   "- Produk Aktif: **{$totalProducts} produk** ({$totalCategories} kategori)\n" .
                   "- Pengguna Terdaftar: **{$userStats['total']} orang** (Bisa login: {$userStats['can_login_total']})\n" .
                   "- Transaksi Pesanan: **{$totalOrders} pesanan** (Total Omset: **Rp " . number_format($totalRevenue, 0, ',', '.') . "**)\n" .
                   "- Dashboard: [Dashboard Admin](" . BASEURL . "/admin)";
        } elseif ($userRole === 'operator') {
            return "Data Operasional Pesanan nnaco_sa (Akses Operator):\n" .
                   "- Total Pesanan: **{$totalOrders} pesanan**\n" .
                   "- Rincian Status: Pending: " . ($orderStats['total_pending'] ?? 0) . ", Processing: " . ($orderStats['total_processing'] ?? 0) . ", Shipped: " . ($orderStats['total_shipped'] ?? 0) . ", Completed: " . ($orderStats['total_completed'] ?? 0) . "\n" .
                   "- Dashboard: [Dashboard Operator](" . BASEURL . "/operator)";
        } elseif ($userRole === 'customer') {
            return "Katalog toko nnaco_sa menyediakan **{$totalProducts} produk aksesoris** dalam **{$totalCategories} kategori** (rentang harga Rp " . number_format($minPrice, 0, ',', '.') . " s/d Rp " . number_format($maxPrice, 0, ',', '.') . "). Menu: [Katalog Produk](" . BASEURL . "/product) • [Riwayat Pesanan](" . BASEURL . "/CustomerController/orders)";
        } else {
            return "Katalog toko nnaco_sa menyediakan **{$totalProducts} produk aksesoris** dalam **{$totalCategories} kategori** (rentang harga Rp " . number_format($minPrice, 0, ',', '.') . " s/d Rp " . number_format($maxPrice, 0, ',', '.') . "). Menu: [Katalog Produk](" . BASEURL . "/product) • [Login](" . BASEURL . "/auth/login)";
        }
    }

    /**
     * Menyelesaikan instance AIServiceInterface secara dinamis berdasarkan konfigurasi tabel settings
     */
    private function resolveAiService(): ?AIServiceInterface {
        $settingModel = $this->model('Setting');
        
        $isEnabled = (string)$settingModel->getSetting('ai_enabled', '1') === '1';
        if (!$isEnabled) {
            return null;
        }

        $provider = strtolower((string)$settingModel->getSetting('ai_provider', 'groq'));
        $model = $settingModel->getSetting('ai_model');
        $temperature = $settingModel->getSetting('ai_temperature');
        $maxTokens = $settingModel->getSetting('ai_max_tokens');
        $instruction = $settingModel->getSetting('ai_system_instruction');

        $apiKey = '';
        if ($provider === 'groq') {
            $apiKey = $settingModel->getSetting('groq_api_key') 
                ?: $settingModel->getSetting('ai_api_key') 
                ?: getenv('GROQ_API_KEY') 
                ?: getenv('AI_API_KEY') 
                ?: ($_ENV['AI_API_KEY'] ?? $_SERVER['AI_API_KEY'] ?? '');
            if (!empty($apiKey) && $apiKey !== 'mock_key_for_now') {
                return new GroqAIProvider($apiKey, $model, $temperature !== null ? (float)$temperature : 0.2, $maxTokens !== null ? (int)$maxTokens : 450, $instruction);
            }
        } elseif ($provider === 'gemini') {
            $apiKey = $settingModel->getSetting('gemini_api_key') 
                ?: $settingModel->getSetting('ai_api_key') 
                ?: getenv('GEMINI_API_KEY') 
                ?: getenv('AI_API_KEY') 
                ?: ($_ENV['AI_API_KEY'] ?? $_SERVER['AI_API_KEY'] ?? '');
            if (!empty($apiKey) && $apiKey !== 'mock_key_for_now') {
                return new GeminiAIProvider($apiKey, $model, $temperature !== null ? (float)$temperature : 0.2, $maxTokens !== null ? (int)$maxTokens : 450, $instruction);
            }
        } elseif ($provider === 'nvidia') {
            $apiKey = $settingModel->getSetting('nvidia_api_key') 
                ?: $settingModel->getSetting('ai_api_key') 
                ?: getenv('NVIDIA_API_KEY') 
                ?: getenv('AI_API_KEY') 
                ?: ($_ENV['AI_API_KEY'] ?? $_SERVER['AI_API_KEY'] ?? '');
            if (!empty($apiKey) && $apiKey !== 'mock_key_for_now') {
                return new NvidiaAIProvider($apiKey, $model, $temperature !== null ? (float)$temperature : 0.2, $maxTokens !== null ? (int)$maxTokens : 450, $instruction);
            }
        }

        return null;
    }
}
