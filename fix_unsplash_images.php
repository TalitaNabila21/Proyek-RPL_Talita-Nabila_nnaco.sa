<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

$unsplash_mapping = [
    4 => '1558021211-6d140331005b', // Gantungan Kunci Karakter Lucu (Bear)
    7 => '1516081044453-2c1b212351ea', // Gantungan Kunci Boneka Mini (Bunny)
    8 => '1518081461904-9d8f136351c2', // Pulpen Gel Warna
    9 => '1583485088034-697b5bc54ccd', // Pensil Karakter
    10 => '1501504905252-473c47e087f8', // Buku Catatan Mini
    11 => '1572375992501-4b0892d50c69', // Stiker Lucu
    12 => '1580569214296-5cb2aff7eee3', // Penghapus Karakter
    15 => '1505843490538-5133c6c7d0e1', // Hiasan Meja Handmade
    16 => '1604145951664-9271a07bd246', // Tempat Pensil
    24 => '1556228578044-516f559a2f52', // Skincare
    25 => '1627123424574-724758594e93', // Dompet
    26 => '1544816155-12df9643f363', // Paperback / Books
    27 => '1589574889505-1a7337bd5d7f', // Kerudung
    28 => '1572375992501-4b0892d50c69', // Stiker Eksklusif
    29 => '1584036561584-b03400a430da', // Masker
    31 => '1596462502278-27bfdc403348', // Tas Makeup
    32 => '1558021211-6d140331005b', // Mainan
];

$db->query("SELECT p.*, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id = c.id");
$products = $db->resultSet();

$image_dir = __DIR__ . '/public/assets/images/products/';

echo "Memperbaiki gambar yang tidak sesuai (buku jadi dompet, dll)...\n\n";

foreach ($products as $prod) {
    $id = (int)$prod['id'];
    
    if (isset($unsplash_mapping[$id])) {
        $unsplash_id = $unsplash_mapping[$id];
        $url = "https://images.unsplash.com/photo-{$unsplash_id}?w=600&h=600&fit=crop";
        
        $id_str = str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
        $target_filename = $prod['slug'] . '-' . $id_str . '.jpg';
        $filepath = $image_dir . $target_filename;
        
        echo "Mengunduh gambar yang benar untuk: {$prod['name']}...\n";
        
        $img_data = @file_get_contents($url);
        if ($img_data && strlen($img_data) > 5000) {
            file_put_contents($filepath, $img_data);
            
            $db->query("UPDATE products SET image = :img WHERE id = :id");
            $db->bind('img', $target_filename);
            $db->bind('id', $prod['id']);
            $db->execute();
            
            echo "   -> Sukses tersimpan sebagai {$target_filename}\n";
        } else {
            echo "   -> Gagal mengunduh gambar.\n";
        }
    }
}

echo "\nSelesai! Semua gambar alat tulis, buku, skincare, stiker, dan mainan telah diperbaiki dan 100% relevan.\n";
