<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$mismatched_ids = [4, 7, 12, 16, 24, 27, 29, 32];
$image_dir = __DIR__ . '/public/assets/images/products/';

// Download a nice aesthetic font (Montserrat or Roboto)
$font_path = __DIR__ . '/public/assets/fonts/Montserrat-Bold.ttf';
if (!file_exists(__DIR__ . '/public/assets/fonts')) {
    mkdir(__DIR__ . '/public/assets/fonts', 0777, true);
}
if (!file_exists($font_path)) {
    // A direct link to Montserrat Bold TrueType font from a reliable CDN
    $font_data = file_get_contents('https://github.com/JulietaUla/Montserrat/raw/master/fonts/ttf/Montserrat-Bold.ttf');
    file_put_contents($font_path, $font_data);
}

// Pastel colors
$colors = [
    [255, 182, 193], // Light Pink
    [176, 224, 230], // Powder Blue
    [221, 160, 221], // Plum/Lavender
    [255, 228, 196], // Bisque/Peach
    [152, 251, 152]  // Pale Green
];

function createAestheticImage($text, $filepath, $font_path, $bg_color) {
    $width = 600;
    $height = 600;
    
    $image = imagecreatetruecolor($width, $height);
    
    $bg = imagecolorallocate($image, $bg_color[0], $bg_color[1], $bg_color[2]);
    imagefill($image, 0, 0, $bg);
    
    $text_color = imagecolorallocate($image, 80, 80, 80); // Dark gray
    $shadow_color = imagecolorallocate($image, 255, 255, 255); // White highlight
    
    // Split text into multiple lines if too long
    $words = explode(' ', $text);
    $lines = [];
    $current_line = '';
    foreach ($words as $word) {
        if (strlen($current_line . ' ' . $word) > 15) {
            $lines[] = $current_line;
            $current_line = $word;
        } else {
            $current_line .= (empty($current_line) ? '' : ' ') . $word;
        }
    }
    $lines[] = $current_line;
    
    $font_size = 40;
    $y_offset = ($height / 2) - ((count($lines) * ($font_size + 20)) / 2) + $font_size;
    
    foreach ($lines as $line) {
        $bbox = imagettfbbox($font_size, 0, $font_path, $line);
        $x = ($width - ($bbox[2] - $bbox[0])) / 2;
        
        // Shadow/Highlight
        imagettftext($image, $font_size, 0, $x+2, $y_offset+2, $shadow_color, $font_path, $line);
        // Text
        imagettftext($image, $font_size, 0, $x, $y_offset, $text_color, $font_path, $line);
        
        $y_offset += $font_size + 20;
    }
    
    imagejpeg($image, $filepath, 95);
    imagedestroy($image);
}

foreach ($mismatched_ids as $index => $id) {
    $db->query("SELECT id, name, slug FROM products WHERE id = :id");
    $db->bind('id', $id);
    $prod = $db->single();
    
    if ($prod) {
        $id_str = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
        $target_filename = $prod['slug'] . '-' . $id_str . '.jpg';
        $filepath = $image_dir . $target_filename;
        
        $color = $colors[$index % count($colors)];
        
        echo "Membuat gambar aesthetic untuk: {$prod['name']}...\n";
        createAestheticImage($prod['name'], $filepath, $font_path, $color);
        
        $db->query("UPDATE products SET image = :img WHERE id = :id");
        $db->bind('img', $target_filename);
        $db->bind('id', $prod['id']);
        $db->execute();
    }
}
echo "Selesai!\n";
