<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$artifact_dir = 'C:/Users/Smoet/.gemini/antigravity-ide/brain/54c95afe-2b16-4f15-bfaa-7926078b3a4d/';
$image_dir = __DIR__ . '/public/assets/images/products/';

$mapping = [
    'Gantungan Kunci Karakter Lucu' => 'aesthetic_keychain_1791443334021.jpg',
    'Gantungan Kunci Boneka Mini' => 'aesthetic_keychain_1791443334021.jpg',
    'Produk Kacamata gaya Eksklusif' => 'aesthetic_sunglasses_1791443365298.jpg',
    'Produk Dompet Eksklusif' => 'aesthetic_wallet_1791442247701.jpg',
    'Produk Skincare Eksklusif' => 'aesthetic_pouch_1791442233145.jpg',
    'Produk Tas makeup Eksklusif' => 'aesthetic_pouch_1791442233145.jpg',
    'Produk Kerudung Eksklusif' => 'aesthetic_pouch_1791442233145.jpg',
    'Produk Masker Eksklusif' => 'aesthetic_pouch_1791442233145.jpg',
    'Produk Mainan Eksklusif' => 'aesthetic_birthday_1791443407234.jpg',
    'Produk Paperback Eksklusif' => 'aesthetic_wallet_1791442247701.jpg',
    'Pensil Karakter' => 'aesthetic_keychain_1791443334021.jpg',
    'Penghapus Karakter' => 'aesthetic_pouch_1791442233145.jpg'
];

foreach ($mapping as $name => $artifact_file) {
    $db->query("SELECT * FROM products WHERE name = :name");
    $db->bind('name', $name);
    $prod = $db->single();
    
    if ($prod) {
        $source = $artifact_dir . $artifact_file;
        $dest = $image_dir . $prod['image'];
        
        if (file_exists($source)) {
            copy($source, $dest);
            echo "Replaced {$prod['name']} with artifact {$artifact_file}\n";
        } else {
            echo "Artifact not found: {$source}\n";
        }
    }
}
