<?php
declare(strict_types=1);

function store_config(): array
{
    $local = [];
    $localFile = __DIR__ . '/config.local.php';
    if (is_file($localFile)) {
        $loaded = require $localFile;
        if (!is_array($loaded)) {
            throw new RuntimeException('config.local.php must return an array.');
        }
        $local = $loaded;
    }

    return array_replace([
        'host' => getenv('STORE_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('STORE_DB_PORT') ?: '3306',
        'name' => getenv('STORE_DB_NAME') ?: 'sunday_club',
        'user' => getenv('STORE_DB_USER') ?: 'root',
        'password' => getenv('STORE_DB_PASSWORD') ?: '',
        'admin_token' => getenv('STORE_ADMIN_TOKEN') ?: '',
        'free_shipping_cents' => 7500,
        'standard_shipping_cents' => 595,
        'express_shipping_cents' => 1295,
    ], $local);
}
