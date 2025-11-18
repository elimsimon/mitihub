<?php
// Define root path for consistent includes across environments
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Simple .env loader
function env_load($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$key] = $value;
    }
}

function env($key, $default=null) {
    return $_ENV[$key] ?? $default;
}

// Load .env if present
$envPath = ROOT_PATH . '/.env';
if (!file_exists($envPath)) {
    $envPath = ROOT_PATH . '/.env.example';
}
env_load($envPath);

// Database connection
function db() : PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $host = env('DB_HOST', 'localhost');
    $db   = env('DB_NAME', 'mitihub');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');
    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Use persistent connections to reduce connection overhead
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}

function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrf_token() {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check($token) {
    start_session();
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function redirect($url) {
    header("Location: $url");
    exit;
}
