<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

// Get all categories
$db->query("SELECT * FROM categories");
$categories = $db->resultSet();

$keywords_map = [
    'gantungan-kunci' => 'keychain',
    'alat-tulis' => 'stationery,pen',
    'kerajinan-tangan' => 'craft,handmade',
    'jepit-rambut' => 'hairclip,accessory',
    'nametag' => 'nametag',
    'gelang' => 'bracelet,jewelry',
    'kacamata-gaya' => 'sunglasses,fashion',
    'bando' => 'headband',
    'pot-hias' => 'decorative,pot,plant',
    'skincare' => 'skincare,cosmetics',
    'dompet' => 'wallet,purse',
    'paperback' => 'paperback,book',
    'kerudung' => 'hijab,scarf',
    'stiker' => 'sticker,cute',
    'kalung' => 'necklace,jewelry',
    'masker' => 'facemask',
    'diy-kawat-bulu' => 'craft,chenille,stems',
    'tas-makeup' => 'makeupbag,pouch',
    'mainan' => 'toy',
    'hiasan-ulang-tahun' => 'birthday,decoration',
    'tumbler' => 'tumbler,bottle'
];

foreach ($categories as $cat) {
    // Check if category has any products
    $db->query("SELECT id FROM products WHERE category_id = :cat_id");
    $db->bind('cat_id', $cat['id']);
    $products = $db->resultSet();
    
    if (count($products) == 0) {
        // Insert a dummy product for this category
        $prod_name = "Produk " . $cat['name'] . " Eksklusif";
        $prod_slug = $cat['slug'] . "-eksklusif";
        $price = rand(15, 150) * 1000;
        
        $db->query("INSERT INTO products (category_id, name, slug, description, price, stock, status) VALUES (:cid, :name, :slug, :desc, :price, :stock, 'active')");
        $db->bind('cid', $cat['id']);
        $db->bind('name', $prod_name);
        $db->bind('slug', $prod_slug);
        $db->bind('desc', "Produk berkualitas tinggi dari kategori " . $cat['name']);
        $db->bind('price', $price);
        $db->bind('stock', rand(10, 50));
        $db->execute();
        echo "Created product for category: {$cat['name']}\n";
    }
}

// Now get all products to update images
$db->query("SELECT * FROM products");
$all_products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';
if (!is_dir($image_dir)) {
    mkdir($image_dir, 0777, true);
}

foreach ($all_products as $prod) {
    // Determine if we need to download an image
    $needs_image = empty($prod['image']) || $prod['image'] == '' || $prod['slug'] == 'hiasan-meja-handmade';
    
    if ($needs_image) {
        // Get category slug to find keyword
        $db->query("SELECT slug FROM categories WHERE id = :cid");
        $db->bind('cid', $prod['category_id']);
        $cat = $db->single();
        
        $keyword = isset($keywords_map[$cat['slug']]) ? $keywords_map[$cat['slug']] : 'accessory,fashion';
        $lock_id = rand(1, 10000); // to get random images per request
        $image_url = "https://loremflickr.com/400/400/" . $keyword . "?lock=" . $lock_id;
        
        $filename = $prod['slug'] . ".jpg";
        $filepath = $image_dir . $filename;
        
        echo "Downloading image for {$prod['name']} (Keyword: $keyword)...\n";
        
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ]
        ]);
        $image_data = @file_get_contents($image_url, false, $context);
        
        if ($image_data) {
            file_put_contents($filepath, $image_data);
            
            // Update database
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            echo "Success: Saved $filename\n";
        } else {
            echo "Failed to download image for {$prod['name']}\n";
        }
    }
}

echo "All done!\n";
