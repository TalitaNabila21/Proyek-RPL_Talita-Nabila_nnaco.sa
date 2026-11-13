<?php
$pdo = new PDO('mysql:host=localhost;dbname=nnaco_sa', 'root', '');
$stmt = $pdo->query('SELECT id, name, price FROM products');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Current Prices:\n";
foreach($products as $p) {
    echo "{$p['name']}: {$p['price']}\n";
}

// Update prices to be cheaper (e.g. cut by half or subtract something)
// Let's divide by 2 and round to nearest 1000
foreach($products as $p) {
    $newPrice = floor(($p['price'] * 0.6) / 1000) * 1000; // 40% discount, rounded to thousands
    $updateStmt = $pdo->prepare('UPDATE products SET price = ? WHERE id = ?');
    $updateStmt->execute([$newPrice, $p['id']]);
}

echo "\nPrices updated successfully to be ~40% cheaper!\n";

$stmt = $pdo->query('SELECT id, name, price FROM products');
$newProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nNew Prices:\n";
foreach($newProducts as $p) {
    echo "{$p['name']}: {$p['price']}\n";
}
