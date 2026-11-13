<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';
require_once 'app/models/Category.php';

$db = new Database();
$categoryModel = new Category();

$new_categories = [
    'Jepit rambut',
    'Gantungan kunci',
    'Nametag',
    'Gelang',
    'Kacamata gaya', // Assuming Kacamana gaya is a typo for Kacamata gaya
    'Bando',
    'Pot hias',
    'Skincare',
    'Dompet',
    'Paperback',
    'Alat tulis',
    'Kerudung',
    'Stiker',
    'Kalung',
    'Masker',
    'DIY kawat bulu',
    'Tas makeup',
    'Mainan', // Assuming Main is a typo for Mainan
    'Hiasan ulang tahun',
    'Tumbler'
];

foreach ($new_categories as $name) {
    // Generate slug
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    
    $db->query("SELECT id FROM categories WHERE slug = :slug");
    $db->bind('slug', $slug);
    $existing = $db->single();
    
    if (!$existing) {
        $cat = [
            'name' => $name,
            'slug' => $slug,
            'description' => 'Kategori ' . $name,
            'status' => 'active'
        ];
        $categoryModel->insert($cat);
        echo "Inserted Category: {$name}\n";
    } else {
        echo "Category exists: {$name}\n";
    }
}

echo "Category seeding completed.\n";
