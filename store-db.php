<?php
declare(strict_types=1);

function store_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = store_config();
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $config['name'])) {
        throw new RuntimeException('STORE_DB_NAME contains invalid characters.');
    }

    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['name']),
        $config['user'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $pdo;
}

function store_json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function store_request_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 16384) {
        store_json(['error' => 'Request is too large.'], 413);
    }
    try {
        $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        store_json(['error' => 'Request body must be valid JSON.'], 400);
    }
    if (!is_array($data)) {
        store_json(['error' => 'Request body must be a JSON object.'], 400);
    }
    return $data;
}

function store_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
    if (!isset($_SESSION['store_csrf'])) {
        $_SESSION['store_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['store_csrf'];
}

function store_require_csrf(): void
{
    $expected = store_csrf_token();
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($provided) || !hash_equals($expected, $provided)) {
        store_json(['error' => 'Your session expired. Refresh the page and try again.'], 403);
    }
}

function store_pkr(int $paisa): string
{
    return 'PKR ' . number_format($paisa / 100, 2, '.', ',');
}
