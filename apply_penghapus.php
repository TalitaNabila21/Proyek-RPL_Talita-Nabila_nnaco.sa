<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$artifact_dir = 'C:/Users/Smoet/.gemini/antigravity-ide/brain/54c95afe-2b16-4f15-bfaa-7926078b3a4d/';
$image_dir = __DIR__ . '/public/assets/images/products/';

$mapping = [
    'Penghapus Karakter' => 'penghapus_cute_1791528685329.jpg'
];

foreach ($mapping as $name => $artifact_file) {
    $db->query("SELECT * FROM products WHERE name = :name");
    $db->bind('name', $name);
    $prod = $db->single();
    
    if ($prod) {
        $source = $artifact_dir . $artifact_file;
        
        if (file_exists($source)) {
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
