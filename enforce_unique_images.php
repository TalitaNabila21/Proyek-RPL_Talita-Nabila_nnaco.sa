<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$keywords_map = [
    'gantungan-kunci' => 'keychain,cute',
    'alat-tulis' => 'stationery,pen',
    'kerajinan-tangan' => 'craft,handmade',
    'jepit-rambut' => 'hairclip,accessory',
    'nametag' => 'nametag,lanyard',
    'gelang' => 'bracelet,jewelry',
    'kacamata-gaya' => 'sunglasses,fashion',
    'bando' => 'headband,cute',
    'pot-hias' => 'decorative,pot,plant',
    'skincare' => 'skincare,cosmetics',
    'dompet' => 'wallet,purse',
    'paperback' => 'paperback,book',
    'kerudung' => 'hijab,scarf',
    'stiker' => 'sticker,cute',
    'kalung' => 'necklace,jewelry',
    'masker' => 'facemask',
    'diy-kawat-bulu' => 'craft,fluffy',
    'tas-makeup' => 'makeupbag,pouch',
    'mainan' => 'toy,cute',
    'hiasan-ulang-tahun' => 'birthday,decoration',
    'tumbler' => 'tumbler,bottle',
    'hiasan-meja' => 'desk,decoration',
    'tempat-pensil' => 'pencilcase',
    'kotak-hadiah' => 'giftbox',
    'cincin' => 'ring,jewelry',
];

$db->query("SELECT p.*, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id = c.id");
$products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';

$summary = [
    'total' => count($products),
    'added' => 0,
    'failed' => 0,
    'already_unique' => 0
];

$used_images = [];

foreach ($products as $prod) {
    $id_str = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
    $target_filename = $prod['slug'] . '-' . $id_str . '.jpg';
    $filepath = $image_dir . $target_filename;
    
    $current_image = $prod['image'];
    $is_shared = in_array($current_image, $used_images);
    
    if ($current_image && file_exists($image_dir . $current_image) && !$is_shared && $current_image !== 'placeholder-product.jpg') {
        // Keep it unique
        $used_images[] = $current_image;
        $summary['already_unique']++;
        echo "Keeping unique image for {$prod['name']}: $current_image\n";
    } else {
        echo "Generating unique image for {$prod['name']}...\n";
        
        $keyword = isset($keywords_map[$prod['cat_slug']]) ? $keywords_map[$prod['cat_slug']] : 'aesthetic,korean';
        
        $name_lower = strtolower($prod['name']);
        if (strpos($name_lower, 'boneka') !== false) $keyword = 'doll,cute';
        if (strpos($name_lower, 'resin') !== false) $keyword = 'resin,art';
        if (strpos($name_lower, 'emas') !== false) $keyword = 'gold,jewelry';
        if (strpos($name_lower, 'mutiara') !== false) $keyword = 'pearl,jewelry';
        if (strpos($name_lower, 'akrilik') !== false) $keyword = 'acrylic,keychain';
        if (strpos($name_lower, 'stiker') !== false) $keyword = 'sticker,cute';
        if (strpos($name_lower, 'penghapus') !== false) $keyword = 'eraser,stationery';
        
        $lock_id = rand(1000, 99999);
        $url = "https://loremflickr.com/500/500/" . $keyword . "?lock=" . $lock_id;
        
        $context = stream_context_create([
            "http" => ["header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n", "timeout" => 15]
        ]);
        
        $img_data = @file_get_contents($url, false, $context);
        
        if ($img_data) {
            file_put_contents($filepath, $img_data);
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $target_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            
            $used_images[] = $target_filename;
            $summary['added']++;
            echo "Success: $target_filename\n";
        } else {
            $summary['failed']++;
            echo "Failed.\n";
        }
    }
}

echo "\n--- SUMMARY ---\n";
echo "Total produk diperiksa: {$summary['total']}\n";
echo "Jumlah produk yang sudah memiliki foto unik: {$summary['already_unique']}\n";
echo "Jumlah foto baru ditambahkan: {$summary['added']}\n";
echo "Jumlah foto gagal ditambahkan: {$summary['failed']}\n";
echo "Folder penyimpanan: public/assets/images/products/\n";
echo "Kolom database: 'image' pada tabel 'products'\n";
echo "File kode yang diubah: app/views/products/index.php\n";
