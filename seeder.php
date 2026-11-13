<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';
require_once 'app/models/Category.php';
require_once 'app/models/Product.php';

$db = new Database();

$categories = [
    ['name' => 'Gantungan Kunci', 'slug' => 'gantungan-kunci', 'description' => 'Berbagai macam gantungan kunci menarik'],
    ['name' => 'Alat Tulis', 'slug' => 'alat-tulis', 'description' => 'Peralatan tulis dan kantor'],
    ['name' => 'Kerajinan Tangan', 'slug' => 'kerajinan-tangan', 'description' => 'Produk kerajinan tangan eksklusif']
];

$categoryModel = new Category();

$categoryIds = [];

foreach ($categories as $cat) {
    // check if exists
    $db->query("SELECT id FROM categories WHERE slug = :slug");
    $db->bind('slug', $cat['slug']);
    $existing = $db->single();
    if (!$existing) {
        $cat['status'] = 'active';
        $categoryModel->insert($cat);
        // get id
        $db->query("SELECT id FROM categories WHERE slug = :slug");
        $db->bind('slug', $cat['slug']);
        $new = $db->single();
        $categoryIds[$cat['slug']] = $new['id'];
        echo "Inserted Category: {$cat['name']}\n";
    } else {
        $categoryIds[$cat['slug']] = $existing['id'];
        echo "Category exists: {$cat['name']}\n";
    }
}

$products = [
    // Gantungan Kunci
    [
        'category_slug' => 'gantungan-kunci',
        'name' => 'Gantungan Kunci Akrilik Custom',
        'slug' => 'gantungan-kunci-akrilik-custom',
        'description' => 'Gantungan kunci akrilik custom dengan desain menarik dan dapat digunakan sebagai hadiah atau koleksi.',
        'price' => 25000,
        'stock' => 20,
        'image' => ''
    ],
    [
        'category_slug' => 'gantungan-kunci',
        'name' => 'Gantungan Kunci Karakter Lucu',
        'slug' => 'gantungan-kunci-karakter-lucu',
        'description' => 'Gantungan kunci dengan karakter-karakter lucu yang disukai anak-anak maupun dewasa.',
        'price' => 15000,
        'stock' => 50,
        'image' => ''
    ],
    [
        'category_slug' => 'gantungan-kunci',
        'name' => 'Gantungan Kunci Resin Handmade',
        'slug' => 'gantungan-kunci-resin-handmade',
        'description' => 'Gantungan kunci resin yang dibuat secara handmade, sangat unik dan eksklusif.',
        'price' => 35000,
        'stock' => 15,
        'image' => ''
    ],
    [
        'category_slug' => 'gantungan-kunci',
        'name' => 'Gantungan Kunci Nama Custom',
        'slug' => 'gantungan-kunci-nama-custom',
        'description' => 'Gantungan kunci yang bisa diukir dengan nama Anda sendiri.',
        'price' => 30000,
        'stock' => 25,
        'image' => ''
    ],
    [
        'category_slug' => 'gantungan-kunci',
        'name' => 'Gantungan Kunci Boneka Mini',
        'slug' => 'gantungan-kunci-boneka-mini',
        'description' => 'Gantungan kunci berbentuk boneka mini yang lembut dan menggemaskan.',
        'price' => 20000,
        'stock' => 30,
        'image' => ''
    ],

    // Alat Tulis
    [
        'category_slug' => 'alat-tulis',
        'name' => 'Pulpen Gel Warna',
        'slug' => 'pulpen-gel-warna',
        'description' => 'Set pulpen gel dengan berbagai warna cerah untuk menulis catatan atau menggambar.',
        'price' => 15000,
        'stock' => 100,
        'image' => ''
    ],
    [
        'category_slug' => 'alat-tulis',
        'name' => 'Pensil Karakter',
        'slug' => 'pensil-karakter',
        'description' => 'Pensil dengan hiasan karakter lucu di ujungnya, cocok untuk anak sekolah.',
        'price' => 5000,
        'stock' => 200,
        'image' => ''
    ],
    [
        'category_slug' => 'alat-tulis',
        'name' => 'Buku Catatan Mini',
        'slug' => 'buku-catatan-mini',
        'description' => 'Buku catatan berukuran saku yang praktis dibawa kemana saja.',
        'price' => 25000,
        'stock' => 40,
        'image' => ''
    ],
    [
        'category_slug' => 'alat-tulis',
        'name' => 'Stiker Lucu',
        'slug' => 'stiker-lucu',
        'description' => 'Koleksi stiker lucu untuk menghias buku catatan atau laptop.',
        'price' => 10000,
        'stock' => 150,
        'image' => ''
    ],
    [
        'category_slug' => 'alat-tulis',
        'name' => 'Penghapus Karakter',
        'slug' => 'penghapus-karakter',
        'description' => 'Penghapus unik berbentuk karakter-karakter makanan atau hewan.',
        'price' => 8000,
        'stock' => 80,
        'image' => ''
    ],

    // Kerajinan Tangan
    [
        'category_slug' => 'kerajinan-tangan',
        'name' => 'Gelang Handmade',
        'slug' => 'gelang-handmade',
        'description' => 'Gelang cantik yang dirangkai secara manual menggunakan manik-manik pilihan.',
        'price' => 25000,
        'stock' => 20,
        'image' => ''
    ],
    [
        'category_slug' => 'kerajinan-tangan',
        'name' => 'Kalung Handmade',
        'slug' => 'kalung-handmade',
        'description' => 'Kalung etnik yang dibuat dengan teliti oleh pengrajin lokal.',
        'price' => 45000,
        'stock' => 15,
        'image' => ''
    ],
    [
        'category_slug' => 'kerajinan-tangan',
        'name' => 'Hiasan Meja Handmade',
        'slug' => 'hiasan-meja-handmade',
        'description' => 'Hiasan meja unik dari bahan daur ulang yang ramah lingkungan.',
        'price' => 75000,
        'stock' => 10,
        'image' => ''
    ],
    [
        'category_slug' => 'kerajinan-tangan',
        'name' => 'Tempat Pensil Handmade',
        'slug' => 'tempat-pensil-handmade',
        'description' => 'Tempat pensil dari anyaman atau kain perca yang fungsional dan estetis.',
        'price' => 35000,
        'stock' => 25,
        'image' => ''
    ],
    [
        'category_slug' => 'kerajinan-tangan',
        'name' => 'Kotak Hadiah Handmade',
        'slug' => 'kotak-hadiah-handmade',
        'description' => 'Kotak kado buatan tangan dengan dekorasi pita dan bunga kertas.',
        'price' => 50000,
        'stock' => 30,
        'image' => ''
    ],
];

$productModel = new Product();
foreach ($products as $prod) {
    $db->query("SELECT id FROM products WHERE slug = :slug");
    $db->bind('slug', $prod['slug']);
    $existing = $db->single();
    if (!$existing) {
        $prod['category_id'] = $categoryIds[$prod['category_slug']];
        $prod['status'] = 'active';
        $productModel->insert($prod);
        echo "Inserted Product: {$prod['name']}\n";
    } else {
        echo "Product exists: {$prod['name']}\n";
    }
}

echo "Seeding completed.\n";
