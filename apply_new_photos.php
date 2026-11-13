<?php
$brain_dir = 'C:/Users/Smoet/.gemini/antigravity-ide/brain/54c95afe-2b16-4f15-bfaa-7926078b3a4d/';
$target_dir = __DIR__ . '/public/assets/images/products/';

$mapping = [
    'diy_kawat_bulu_1791522995726.jpg' => 'diy-kawat-bulu-eksklusif-030.jpg',
    'bando_eksklusif_1791523021394.jpg' => 'bando-eksklusif-022.jpg',
    'gelang_handmade_1791523047574.jpg' => 'gelang-handmade-013.jpg',
    'kotak_hadiah_handmade_1791523058962.jpg' => 'kotak-hadiah-handmade-017.jpg',
    'hiasan_meja_1791523080298.jpg' => 'hiasan-meja-handmade-015.jpg',
    'tempat_pensil_handmade_1791523091901.jpg' => 'tempat-pensil-handmade-016.jpg',
    'kalung_mutiara_1791523113590.jpg' => 'kalung-mutiara-001.jpg',
    'kalung_handmade_1791523127535.jpg' => 'kalung-handmade-014.jpg'
];

foreach ($mapping as $source => $dest) {
    $src_path = $brain_dir . $source;
    $dst_path = $target_dir . $dest;
    
    if (file_exists($src_path)) {
        if (copy($src_path, $dst_path)) {
            echo "Copied $source to $dest\n";
        } else {
            echo "Failed to copy $source\n";
        }
    } else {
        echo "Source not found: $source\n";
    }
}
