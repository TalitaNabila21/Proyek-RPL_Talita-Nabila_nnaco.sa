<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

// Get all products
$db->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id");
$products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';
if (!is_dir($image_dir)) {
    mkdir($image_dir, 0777, true);
}

$summary = [
    'total' => count($products),
    'added' => 0,
    'failed' => 0,
    'already_unique' => 0
];

$existing_images = [];

echo "Starting image generation for " . count($products) . " products...\n";

foreach ($products as $prod) {
    $id = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
    $target_filename = $prod['slug'] . '-' . $id . '.jpg';
    $filepath = $image_dir . $target_filename;
    
    // Always generate a unique one if the current one is not the target filename
    if ($prod['image'] !== $target_filename || !file_exists($filepath)) {
        
        $prompt_text = "A cute aesthetic premium korean style " . $prod['name'] . " product photography on a soft pastel background, highly detailed";
        $prompt = urlencode($prompt_text);
        
        $url = "https://image.pollinations.ai/prompt/{$prompt}?width=600&height=600&nologo=true";
        
        echo "Generating image for: {$prod['name']}...\n";
        
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                "timeout" => 30
            ]
        ]);
        
        $image_data = @file_get_contents($url, false, $context);
        
        if ($image_data && strlen($image_data) > 1000) {
            file_put_contents($filepath, $image_data);
            
            // Update database
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $target_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            
            $summary['added']++;
            echo "Success: Saved $target_filename\n";
        } else {
            $summary['failed']++;
            echo "Failed to generate image for {$prod['name']}\n";
        }
        
        // Add a small delay to avoid overwhelming the free API
        usleep(500000); 
    } else {
        $summary['already_unique']++;
        echo "Product {$prod['name']} already has unique image $target_filename\n";
    }
}

echo "\n--- SUMMARY ---\n";
echo "Total produk diperiksa: {$summary['total']}\n";
echo "Jumlah produk yang sudah memiliki foto unik: {$summary['already_unique']}\n";
echo "Jumlah foto baru ditambahkan: {$summary['added']}\n";
echo "Jumlah foto gagal ditambahkan: {$summary['failed']}\n";
echo "Folder penyimpanan: public/assets/images/products/\n";
echo "Kolom database: 'image' pada tabel 'products'\n";
