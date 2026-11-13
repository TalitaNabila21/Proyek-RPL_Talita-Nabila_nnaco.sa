<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();
$db->query("SELECT p.*, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id = c.id");
$products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';

$used_images = [];
$added = 0;
$failed = 0;

foreach ($products as $prod) {
    $id_str = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
    $target_filename = $prod['slug'] . '-' . $id_str . '.jpg';
    $filepath = $image_dir . $target_filename;
    
    $current_image = $prod['image'];
    $is_shared = in_array($current_image, $used_images);
    
    if ($current_image && file_exists($image_dir . $current_image) && !$is_shared && $current_image !== 'placeholder-product.jpg') {
        // Unique image
        $used_images[] = $current_image;
        echo "Keeping unique image for {$prod['name']}: $current_image\n";
    } else {
        echo "Generating unique image for {$prod['name']}...\n";
        
        $prompt = "A very aesthetic, beautiful, premium korean style " . $prod['name'] . " product photography on a soft background, highly detailed";
        $prompt = urlencode($prompt);
        // Add random seed to prevent caching issues
        $seed = rand(1000, 99999);
        $url = "https://image.pollinations.ai/prompt/{$prompt}?width=600&height=600&nologo=true&seed={$seed}";
        
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n",
                "timeout" => 30
            ]
        ]);
        
        $img_data = @file_get_contents($url, false, $context);
        
        if ($img_data && strlen($img_data) > 1000) {
            file_put_contents($filepath, $img_data);
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $target_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            
            $used_images[] = $target_filename;
            $added++;
            echo "Success: $target_filename\n";
        } else {
            $failed++;
            echo "Failed to download.\n";
        }
        
        sleep(2); // Wait 2 seconds
    }
}

echo "Added: $added, Failed: $failed\n";
