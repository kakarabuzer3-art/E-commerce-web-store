<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/store-db.php';
require __DIR__ . '/countries.php';
store_csrf_token();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    header('Allow: GET, POST, PUT, DELETE, OPTIONS');
    http_response_code(204);
    exit;
}

$config = store_config();
$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $pdo = store_db();

    if ($method === 'GET' && $route === 'csrf') {
        store_json(['token' => store_csrf_token()]);
    }

    if ($method === 'GET' && $route === 'products') {
        $statement = $pdo->query('SELECT id,sku,name,category,product_type AS type,description AS `desc`,image_path AS image,badge,rating,price_cents,stock_quantity AS stock,active FROM sc_products WHERE active=1 ORDER BY id');
        $products = array_map(static function (array $product): array {
            $product['id'] = (int) $product['id'];
            $product['price'] = (int) $product['price_cents'] / 100;
            $product['stock'] = (int) $product['stock'];
            $product['rating'] = (float) $product['rating'];
            $product['color'] = '#e8e7df';
            unset($product['price_cents'], $product['active']);
            return $product;
        }, $statement->fetchAll());
        store_json(['products' => $products]);
    }

    if ($method === 'GET' && $route === 'cart') {
        $ids = array_keys($_SESSION['store_cart'] ?? []);
        $items = [];
        if ($ids !== []) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $statement = $pdo->prepare("SELECT id,name,product_type AS type,image_path AS image,price_cents,stock_quantity AS stock FROM sc_products WHERE active=1 AND id IN ($placeholders)");
            $statement->execute($ids);
            foreach ($statement->fetchAll() as $product) {
                $quantity = (int) $_SESSION['store_cart'][$product['id']];
                if ($quantity < 1 || $quantity > 10) {
                    unset($_SESSION['store_cart'][$product['id']]);
                    continue;
                }
                $items[] = [
                    'id' => (int) $product['id'],
                    'name' => $product['name'],
                    'type' => $product['type'],
                    'image' => $product['image'],
                    'price' => (int) $product['price_cents'] / 100,
                    'stock' => (int) $product['stock'],
                    'quantity' => $quantity,
                ];
            }
        }
        $subtotalCents = array_reduce($items, static fn(int $sum, array $item): int => $sum + (int) round($item['price'] * 100) * $item['quantity'], 0);
        $standard = $subtotalCents >= $config['free_shipping_cents'] ? 0 : $config['standard_shipping_cents'];
        store_json([
            'items' => $items,
            'subtotal' => $subtotalCents / 100,
            'shipping' => ['standard' => $standard / 100, 'express' => $config['express_shipping_cents'] / 100],
            'freeShippingThreshold' => $config['free_shipping_cents'] / 100,
        ]);
    }

    if ($method === 'GET' && $route === 'shipping') {
        $subtotal = filter_input(INPUT_GET, 'subtotal', FILTER_VALIDATE_FLOAT);
        if ($subtotal === false || $subtotal === null || $subtotal < 0 || $subtotal > 100000) {
            store_json(['error' => 'A valid order subtotal is required.'], 400);
        }
        $subtotalCents = (int) round($subtotal * 100);
        store_json([
            'freeShippingThreshold' => $config['free_shipping_cents'] / 100,
            'standard' => ['name' => 'Standard delivery', 'description' => '3–5 business days', 'price' => ($subtotalCents >= $config['free_shipping_cents'] ? 0 : $config['standard_shipping_cents']) / 100],
            'express' => ['name' => 'Express delivery', 'description' => '1–2 business days', 'price' => $config['express_shipping_cents'] / 100],
        ]);
    }

    if (in_array($method, ['POST', 'PUT', 'DELETE'], true) && in_array($route, ['cart', 'checkout', 'track'], true)) {
        store_require_csrf();
    }

    if ($method === 'POST' && $route === 'cart') {
        $input = store_request_json();
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($input['quantity'] ?? 1, FILTER_VALIDATE_INT);
        if (!$id || !$quantity || $quantity < 1 || $quantity > 10) {
            store_json(['error' => 'Choose a valid product and a quantity from 1 to 10.'], 400);
        }
        $check = $pdo->prepare('SELECT stock_quantity FROM sc_products WHERE id=? AND active=1');
        $check->execute([$id]);
        $product = $check->fetch();
        if (!$product) {
            store_json(['error' => 'That product is unavailable.'], 404);
        }
        $newQuantity = (int) ($_SESSION['store_cart'][$id] ?? 0) + $quantity;
        if ($newQuantity > 10 || $newQuantity > (int) $product['stock_quantity']) {
            store_json(['error' => 'There is not enough stock to add that quantity.'], 409);
        }
        $_SESSION['store_cart'][$id] = $newQuantity;
        store_json(['ok' => true]);
    }

    if ($method === 'PUT' && $route === 'cart') {
        $input = store_request_json();
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $quantity === false || $quantity < 0 || $quantity > 10) {
            store_json(['error' => 'Choose a valid product quantity.'], 400);
        }
        if ($quantity === 0) {
            unset($_SESSION['store_cart'][$id]);
            store_json(['ok' => true]);
        }
        $check = $pdo->prepare('SELECT stock_quantity FROM sc_products WHERE id=? AND active=1');
        $check->execute([$id]);
        $product = $check->fetch();
        if (!$product || $quantity > (int) $product['stock_quantity']) {
            store_json(['error' => 'That quantity is not available in stock.'], 409);
        }
        $_SESSION['store_cart'][$id] = $quantity;
        store_json(['ok' => true]);
    }

    if ($method === 'DELETE' && $route === 'cart') {
        $input = store_request_json();
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            store_json(['error' => 'Choose a valid product.'], 400);
        }
        unset($_SESSION['store_cart'][$id]);
        store_json(['ok' => true]);
    }

    if ($method === 'POST' && $route === 'checkout') {
        $input = store_request_json();
        $customer = $input['customer'] ?? null;
        if (!is_array($customer)) {
            store_json(['error' => 'Enter your delivery details.'], 400);
        }
        $clean = static fn(mixed $value, int $max): string => is_string($value) ? mb_substr(trim($value), 0, $max) : '';
        $address = [
            'name' => $clean($customer['name'] ?? '', 100),
            'email' => mb_strtolower($clean($customer['email'] ?? '', 200)),
            'phone' => $clean($customer['phone'] ?? '', 30),
            'address1' => $clean($customer['address1'] ?? '', 180),
            'address2' => $clean($customer['address2'] ?? '', 120),
            'city' => $clean($customer['city'] ?? '', 100),
            'state' => '',
            'postalCode' => '',
            'country' => strtoupper($clean($customer['country'] ?? '', 2)),
        ];
        $countries = store_countries();
        if (!array_key_exists($address['country'], $countries)) {
            store_json(['error' => 'Choose a country from the list.'], 400);
        }
        $shippingMethod = $input['shipping'] ?? '';
        if (!in_array($shippingMethod, ['standard', 'express'], true)) {
            store_json(['error' => 'Choose a delivery option.'], 400);
        }
        $cart = $_SESSION['store_cart'] ?? [];
        if ($cart === [] || count($cart) > 22) {
            store_json(['error' => 'Your bag is empty. Add some gear before checking out.'], 400);
        }

        try {
            $pdo->beginTransaction();
            $findProduct = $pdo->prepare('SELECT id,sku,name,price_cents,stock_quantity FROM sc_products WHERE id=? AND active=1 FOR UPDATE');
            $decreaseStock = $pdo->prepare('UPDATE sc_products SET stock_quantity=stock_quantity-? WHERE id=? AND stock_quantity>=?');
            $insertOrder = $pdo->prepare(
                'INSERT INTO sc_orders
                (order_number,tracking_access_hash,customer_name,customer_email,customer_phone,shipping_address_1,shipping_address_2,shipping_city,shipping_state,shipping_postal_code,shipping_country,shipping_method,shipping_status,payment_method,payment_status,currency,subtotal_cents,shipping_cents,tax_cents,total_cents,customer_note)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"processing","cash_on_delivery","pending","PKR",?,?,0,?,?)'
            );
            $insertLine = $pdo->prepare('INSERT INTO sc_order_items(order_id,product_id,sku,product_name,quantity,unit_price_cents,line_total_cents) VALUES(?,?,?,?,?,?,?)');
            $lines = [];
            $subtotal = 0;
            foreach ($cart as $id => $quantity) {
                if (!ctype_digit((string) $id) || !is_int($quantity) || $quantity < 1 || $quantity > 10) {
                    throw new DomainException('Your bag contains an invalid quantity. Refresh and try again.');
                }
                $findProduct->execute([(int) $id]);
                $product = $findProduct->fetch();
                if (!$product) {
                    throw new DomainException('A product in your bag is no longer available.');
                }
                if ((int) $product['stock_quantity'] < $quantity) {
                    throw new DomainException($product['name'] . ' does not have enough stock for this order.');
                }
                $lineCents = (int) $product['price_cents'] * $quantity;
                $subtotal += $lineCents;
                $lines[] = ['product' => $product, 'quantity' => $quantity, 'lineCents' => $lineCents];
            }
            $shippingCents = $shippingMethod === 'express'
                ? $config['express_shipping_cents']
                : ($subtotal >= $config['free_shipping_cents'] ? 0 : $config['standard_shipping_cents']);
            $total = $subtotal + $shippingCents;
            $orderNumber = 'SC-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $accessCode = strtoupper(bin2hex(random_bytes(6)));
            $insertOrder->execute([
                $orderNumber,
                hash('sha256', $accessCode),
                $address['name'],
                $address['email'],
                $address['phone'],
                $address['address1'],
                $address['address2'],
                $address['city'],
                $address['state'],
                $address['postalCode'],
                $address['country'],
                $shippingMethod,
                $subtotal,
                $shippingCents,
                $total,
                $clean($customer['note'] ?? '', 2000),
            ]);
            $orderId = (int) $pdo->lastInsertId();
            foreach ($lines as $line) {
                $product = $line['product'];
                $quantity = $line['quantity'];
                $insertLine->execute([$orderId, $product['id'], $product['sku'], $product['name'], $quantity, $product['price_cents'], $line['lineCents']]);
                $decreaseStock->execute([$quantity, $product['id'], $quantity]);
                if ($decreaseStock->rowCount() !== 1) {
                    throw new DomainException('Stock changed while you were checking out. Please try again.');
                }
            }
            $pdo->commit();
            $_SESSION['store_cart'] = [];
            store_json([
                'orderNumber' => $orderNumber,
                'accessCode' => $accessCode,
                'status' => 'processing',
                'paymentMethod' => 'cash_on_delivery',
                'shippingMethod' => $shippingMethod,
                'shippingAmount' => $shippingCents / 100,
                'subtotal' => $subtotal / 100,
                'tax' => 0,
                'total' => $total / 100,
                'estimatedDelivery' => $shippingMethod === 'express' ? '1–2 business days' : '3–5 business days',
            ], 201);
        } catch (DomainException $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            store_json(['error' => $error->getMessage()], 409);
        }
    }

    if ($method === 'POST' && $route === 'track') {
        $input = store_request_json();
        $number = strtoupper(trim((string) ($input['orderNumber'] ?? '')));
        $code = strtoupper(trim((string) ($input['accessCode'] ?? '')));
        if ($number === '' || $code === '') {
            store_json(['error' => 'Enter your order number and order access code.'], 400);
        }
        $statement = $pdo->prepare('SELECT id,order_number,tracking_access_hash,shipping_method,shipping_status,carrier_name,tracking_number,shipping_cents,total_cents,created_at FROM sc_orders WHERE order_number=?');
        $statement->execute([$number]);
        $order = $statement->fetch();
        if (!$order || !hash_equals($order['tracking_access_hash'], hash('sha256', $code))) {
            store_json(['error' => 'No order matched that order number and access code.'], 404);
        }
        $items = $pdo->prepare('SELECT product_name AS name,quantity FROM sc_order_items WHERE order_id=?');
        $items->execute([$order['id']]);
        unset($order['id'], $order['tracking_access_hash']);
        $order['orderNumber'] = $order['order_number'];
        $order['items'] = $items->fetchAll();
        $order['shippingAmount'] = (int) $order['shipping_cents'] / 100;
        $order['total'] = (int) $order['total_cents'] / 100;
        store_json($order);
    }

    store_json(['error' => 'API route not found.'], 404);
} catch (PDOException $error) {
    error_log('Store database error: ' . $error->getMessage());
    store_json(['error' => 'The store database is unavailable. Confirm config.php and run php setup.php.'], 503);
} catch (Throwable $error) {
    error_log('Store API error: ' . $error->getMessage());
    store_json(['error' => 'The store could not complete that request.'], 500);
}
