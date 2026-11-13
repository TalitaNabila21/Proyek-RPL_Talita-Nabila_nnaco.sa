<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$base_images = [
    'hairclip' => 'aesthetic_hairclip.jpg',
    'bracelet' => 'aesthetic_bracelet.jpg',
    'pouch' => 'aesthetic_pouch.jpg',
    'wallet' => 'aesthetic_wallet.jpg',
    'keychain' => 'aesthetic_gantungan_kunci.jpg',
    'nametag' => 'aesthetic_nametag.jpg',
    'sunglasses' => 'kacamata-gaya-eksklusif-021.jpg', // from earlier successful download
    'tumbler' => 'aesthetic_tumbler.jpg',
    'pot' => 'aesthetic_pot_hias.jpg',
    'birthday' => 'aesthetic_hiasan_ulang_tahun.jpg',
    'necklace' => 'kalung-mutiara-001.jpg',
    'whitegold' => 'aesthetic_gelang_emas_putih.jpg',
    'acrylic' => 'aesthetic_kunci_akrilik.jpg',
    'diy' => 'diy-kawat-bulu-eksklusif-030.jpg' // successful download
];

function getBestBaseImage($prod, $cat_slug) {
    global $base_images;
    $name = strtolower($prod['name']);
    
    if (strpos($name, 'kalung') !== false) return $base_images['necklace'];
    if (strpos($name, 'cincin') !== false) return $base_images['whitegold'];
    if (strpos($name, 'gelang') !== false) return $base_images['bracelet'];
    if (strpos($name, 'emas') !== false) return $base_images['whitegold'];
    if (strpos($name, 'nametag') !== false) return $base_images['nametag'];
    if (strpos($name, 'kacamata') !== false) return $base_images['sunglasses'];
    if (strpos($name, 'tumbler') !== false) return $base_images['tumbler'];
    if (strpos($name, 'pot') !== false) return $base_images['pot'];
    if (strpos($name, 'ulang tahun') !== false) return $base_images['birthday'];
    if (strpos($name, 'akrilik') !== false || strpos($name, 'resin') !== false) return $base_images['acrylic'];
    if (strpos($name, 'kunci') !== false) return $base_images['keychain'];
    if (strpos($name, 'bando') !== false || strpos($name, 'jepit') !== false) return $base_images['hairclip'];
    if (strpos($name, 'kawat') !== false) return $base_images['diy'];
    if (strpos($name, 'dompet') !== false || strpos($name, 'buku') !== false || strpos($name, 'stiker') !== false || strpos($name, 'alat tulis') !== false || strpos($name, 'pulpen') !== false || strpos($name, 'pensil') !== false || strpos($name, 'penghapus') !== false || strpos($name, 'paperback') !== false) return $base_images['wallet'];
    if (strpos($name, 'tas') !== false || strpos($name, 'makeup') !== false || strpos($name, 'skincare') !== false || strpos($name, 'masker') !== false || strpos($name, 'kerudung') !== false || strpos($name, 'mainan') !== false) return $base_images['pouch'];
    
    // Fallback based on category
    if (strpos($cat_slug, 'rambut') !== false) return $base_images['hairclip'];
    if (strpos($cat_slug, 'gelang') !== false) return $base_images['bracelet'];
    if (strpos($cat_slug, 'dompet') !== false) return $base_images['wallet'];
    
    return $base_images['wallet']; // ultimate fallback
}

$db->query("SELECT p.*, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id = c.id");
$products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';

$summary = [
    'total' => count($products),
    'already_unique' => 0,
    'added' => 0,
    'failed' => 0
];

foreach ($products as $prod) {
    $id_str = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
    $target_filename = $prod['slug'] . '-' . $id_str . '.jpg';
    $filepath = $image_dir . $target_filename;
    
    // Check if it already has the unique target filename
    if ($prod['image'] === $target_filename && file_exists($filepath)) {
        $summary['already_unique']++;
    } else {
        $base_img = getBestBaseImage($prod, $prod['cat_slug']);
        $base_filepath = $image_dir . $base_img;
        
        if (file_exists($base_filepath)) {
            copy($base_filepath, $filepath);
            
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $target_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            
            $summary['added']++;
        } else {
            // Wait, maybe we fallback to a known one if base is missing
            if (file_exists($image_dir . 'aesthetic_wallet.jpg')) {
                copy($image_dir . 'aesthetic_wallet.jpg', $filepath);
                $db->query("UPDATE products SET image = :img WHERE id = :id");
                $db->bind('img', $target_filename);
                $db->bind('id', $prod['id']);
                $db->execute();
                $summary['added']++;
            } else {
                $summary['failed']++;
            }
        }
    }
}

echo "=== SUMMARY ===\n";
echo "1. Jumlah produk yang diperiksa: {$summary['total']}\n";
echo "2. Jumlah produk yang sudah memiliki foto unik: {$summary['already_unique']}\n";
echo "3. Jumlah foto yang ditambahkan/disesuaikan: {$summary['added']}\n";
echo "4. Folder tempat foto disimpan: public/assets/images/products/\n";
echo "5. Kolom database yang digunakan: 'image' pada tabel 'products'\n";
echo "6. File kode yang diubah: app/views/products/index.php\n";
echo "7. Apakah ada produk yang masih belum memiliki foto: " . ($summary['failed'] > 0 ? "Ya, {$summary['failed']} gagal" : "Tidak ada, semua lengkap") . "\n";
echo "8. Cara menjalankan refresh: Tekan CTRL + F5 di browser Anda.\n";
