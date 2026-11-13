<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();
$db->query("SELECT * FROM products");
$products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';

echo "Checking " . count($products) . " products for text placeholders...\n";
$count = 0;

foreach ($products as $prod) {
    if (!$prod['image']) continue;
    
    $filepath = $image_dir . $prod['image'];
    if (file_exists($filepath)) {
        $size = filesize($filepath);
        if ($size < 50000) { // Less than 50KB is probably the text placeholder
            echo "Regenerating {$prod['name']} (Size: " . round($size/1024) . "KB)...\n";
            
            $prompt_text = "cute aesthetic premium {$prod['name']} product photography, pastel light blue and pink background, highly detailed, photorealistic 8k";
            $prompt = urlencode($prompt_text);
            
            $url = "https://image.pollinations.ai/prompt/{$prompt}?width=600&height=600&nologo=true&enhance=true";
            
            $context = stream_context_create([
                "http" => [
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                    "timeout" => 30
                ]
            ]);
            
            $image_data = @file_get_contents($url, false, $context);
            if ($image_data && strlen($image_data) > 50000) {
                file_put_contents($filepath, $image_data);
                echo "Success: New size " . round(strlen($image_data)/1024) . "KB\n";
                $count++;
            } else {
                echo "Failed or got another small image.\n";
            }
            usleep(1000000); // 1s delay
        }
    }
}
echo "Done! Regenerated $count images.\n";
