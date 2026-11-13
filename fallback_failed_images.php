<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$fallback_map = [
    4 => 'aesthetic_gantungan_kunci.jpg',
    7 => 'aesthetic_gantungan_kunci.jpg',
    12 => 'aesthetic_pouch.jpg',
    16 => 'aesthetic_pouch.jpg',
    24 => 'aesthetic_pot_hias.jpg',
    27 => 'aesthetic_pouch.jpg',
    29 => 'aesthetic_pouch.jpg',
    32 => 'aesthetic_gantungan_kunci.jpg'
];

$image_dir = __DIR__ . '/public/assets/images/products/';

foreach ($fallback_map as $id => $base_img) {
    $db->query("SELECT slug, id FROM products WHERE id = :id");
    $db->bind('id', $id);
    $prod = $db->single();
    
    if ($prod) {
        $id_str = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
        $target_filename = $prod['slug'] . '-' . $id_str . '.jpg';
        $filepath = $image_dir . $target_filename;
        $base_filepath = $image_dir . $base_img;
        
        if (file_exists($base_filepath)) {
            copy($base_filepath, $filepath);
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $target_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            echo "Fallback success for ID $id\n";
        }
    }
}
