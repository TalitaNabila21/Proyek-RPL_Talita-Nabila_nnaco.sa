<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$artifact_dir = 'C:/Users/Smoet/.gemini/antigravity-ide/brain/54c95afe-2b16-4f15-bfaa-7926078b3a4d/';
$image_dir = __DIR__ . '/public/assets/images/products/';

$mapping = [
    'Produk Skincare Eksklusif' => 'skincare_cute_1791528459392.jpg',
    
    'Produk Masker Eksklusif' => 'masker_cute_1791528479171.jpg',
    
    'Produk Kerudung Eksklusif' => 'kerudung_cute_1791528503921.jpg',
    
    'Produk Mainan Eksklusif' => 'mainan_cute_1791528524958.jpg',
    
    'Gantungan Kunci Karakter Lucu' => 'gantungan_kunci_cute_1791528553923.jpg',
    'Gantungan Kunci Boneka Mini' => 'gantungan_kunci_cute_1791528553923.jpg',
    'Gantungan Kunci Akrilik Custom' => 'gantungan_kunci_cute_1791528553923.jpg',
    'Gantungan Kunci Resin Handmade' => 'gantungan_kunci_cute_1791528553923.jpg',
    'Gantungan Kunci Nama Custom' => 'gantungan_kunci_cute_1791528553923.jpg'
];

foreach ($mapping as $name => $artifact_file) {
    $db->query("SELECT * FROM products WHERE name = :name");
    $db->bind('name', $name);
    $prod = $db->single();
    
    if ($prod) {
        $source = $artifact_dir . $artifact_file;
        
        if (file_exists($source)) {
            // Kita harus tetap menggunakan nama file lama agar tidak mengubah database
            // Atau, update databasenya jika ingin pakai nama baru.
            // Biar gampang, kita update aja di DB.
            $new_filename = $artifact_file; 
            $dest = $image_dir . $new_filename;
            
            copy($source, $dest);
            
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $new_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            
            echo "Updated {$prod['name']} with new image {$new_filename}\n";
        } else {
            echo "Artifact not found: {$source}\n";
        }
    } else {
        echo "Product not found: {$name}\n";
    }
}
