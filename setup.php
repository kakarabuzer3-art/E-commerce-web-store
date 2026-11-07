<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found\n");
}

require __DIR__ . '/config.php';
require __DIR__ . '/store-db.php';
$pdo = store_db();
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Cannot read schema.sql.');
}
$pdo->exec($schema);
$pdo->exec("ALTER TABLE sc_orders MODIFY currency CHAR(3) NOT NULL DEFAULT 'PKR'");
$pdo->exec("UPDATE sc_orders SET currency='PKR' WHERE currency<>'PKR'");
$pdo->exec("ALTER TABLE sc_orders MODIFY shipping_status ENUM('unfulfilled','processing','packed','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'processing'");
$pdo->exec("ALTER TABLE sc_orders MODIFY customer_note TEXT NOT NULL");
foreach (['out_for_delivery_at', 'delivered_at'] as $column) {
    $checkColumn = $pdo->query("SHOW COLUMNS FROM sc_orders LIKE '$column'");
    if (!$checkColumn->fetch()) {
        $pdo->exec("ALTER TABLE sc_orders ADD COLUMN `$column` DATETIME NULL");
    }
}
$checkProductRemovedAt = $pdo->query("SHOW COLUMNS FROM sc_products LIKE 'removed_at'");
if (!$checkProductRemovedAt->fetch()) {
    $pdo->exec('ALTER TABLE sc_products ADD COLUMN removed_at DATETIME NULL');
}
$products = require __DIR__ . '/products.php';
$insert = $pdo->prepare(
    'INSERT INTO sc_products (id,sku,name,slug,category,product_type,description,image_path,badge,rating,price_cents,stock_quantity,active)
     VALUES (:id,:sku,:name,:slug,:category,:product_type,:description,:image_path,:badge,:rating,:price_cents,:stock_quantity,:active)
     ON DUPLICATE KEY UPDATE sku=VALUES(sku),name=VALUES(name),slug=VALUES(slug),category=VALUES(category),
     product_type=VALUES(product_type),description=VALUES(description),image_path=VALUES(image_path),
     badge=VALUES(badge),rating=VALUES(rating),price_cents=VALUES(price_cents),active=VALUES(active),removed_at=NULL'
);
foreach ($products as $product) {
    $insert->execute([
        'id' => $product['id'],
        'sku' => $product['sku'],
        'name' => $product['name'],
        'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($product['name'])) ?? '', '-'),
        'category' => $product['category'],
        'product_type' => $product['type'],
        'description' => $product['description'],
        'image_path' => $product['image'],
        'badge' => $product['badge'],
        'rating' => $product['rating'],
        'price_cents' => (int) round($product['price'] * 100),
        'stock_quantity' => $product['stock'],
        'active' => $product['active'],
    ]);
}
echo 'Database schema ready; ' . count($products) . " products are available.\n";
