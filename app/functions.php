<?php
// Consolidated functions from includes/functions.php and includes/helpers.php

function base_url($path = '') {
    static $config = null;
    if ($config === null) {
        $config = require ROOT_PATH . '/config.php';
    }
    $base = rtrim($config['base_url'], '/');
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

// Return an absolute URL including scheme and host. Useful when relative paths fail due to server config.
function full_base_url($path = '') {
    $scheme = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $p = '/' . ltrim(base_url($path), '/');
    return $scheme . '://' . $host . $p;
}

// Simple file-based cache for full-page or fragment caching.
function file_cache_get($key, $ttl = 30) {
    $dir = ROOT_PATH . '/storage/cache';
    if (!is_dir($dir)) return false;
    $file = $dir . '/' . preg_replace('/[^a-z0-9_\\-]/i', '_', $key) . '.html';
    if (!file_exists($file)) return false;
    if (filemtime($file) + $ttl < time()) return false;
    return file_get_contents($file);
}

function file_cache_set($key, $content) {
    $dir = ROOT_PATH . '/storage/cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/' . preg_replace('/[^a-z0-9_\\-]/i', '_', $key) . '.html';
    // write atomically
    $tmp = $file . '.' . bin2hex(random_bytes(6));
    file_put_contents($tmp, $content);
    @rename($tmp, $file);
}

function csrf_token_global() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_check_global($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

// User registration for school/sponsor
function register_user($name, $email, $password, $role) {
    $pdo = db();
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status, setup_complete) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$name, $email, $hash, $role, 'pending_approval', 0]);
    $user_id = (int)$pdo->lastInsertId();
    if ($role === 'school') {
        $stmt = $pdo->prepare('INSERT INTO schools (user_id, school_name) VALUES (?,?)');
        $stmt->execute([$user_id, '']);
    } elseif ($role === 'sponsor') {
        $stmt = $pdo->prepare('INSERT INTO sponsors (user_id, organization) VALUES (?,?)');
        $stmt->execute([$user_id, '']);
    }
    return $user_id;
}

function users_pending_approval() {
    $pdo = db();
    return $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE status='pending_approval' ORDER BY created_at ASC")->fetchAll();
}

function user_set_status($id, $status) {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE users SET status=? WHERE id=?');
    return $stmt->execute([$status, $id]);
}

function user_mark_setup_complete($id) {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE users SET setup_complete=1 WHERE id=?');
    return $stmt->execute([$id]);
}

function user_get_by_email($email) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Generate a human-friendly temporary password of given length
function generate_temp_password($length = 10) {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $bytes = random_bytes($length);
    $temp = '';
    $len = strlen($alphabet);
    for ($i = 0; $i < $length; $i++) {
        $temp .= $alphabet[ord($bytes[$i]) % $len];
    }
    return $temp;
}

// Set a user's password with configurable cost (reads PASSWORD_COST from env or defaults to 10)
function set_user_password($user_id, $plain_password) {
    $pdo = db();
    $cost = (int) (env('PASSWORD_COST', 10));
    $options = ['cost' => max(4, min(14, $cost))];
    $start = microtime(true);
    $hash = password_hash($plain_password, PASSWORD_DEFAULT, $options);
    $took = microtime(true) - $start;
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $res = $stmt->execute([$hash, $user_id]);
    // If hashing/update took too long, log for diagnostics
    if ($took > 1.0) {
        error_log(sprintf("[mitihub] slow_password_hash/update user_id=%s took=%.3fs", $user_id, $took));
    }
    return $res;
}

/**
 * Validate password strength according to site policy.
 * Returns [bool, message] where bool indicates validity.
 */
function validate_password_strength($password) {
    $pw = (string)$password;
    $min = 8;
    if (strlen($pw) < $min) {
        return [false, "Password must be at least {$min} characters long."];
    }
    if (!preg_match('/[a-z]/', $pw)) return [false, 'Password must include at least one lowercase letter.'];
    if (!preg_match('/[A-Z]/', $pw)) return [false, 'Password must include at least one uppercase letter.'];
    if (!preg_match('/[0-9]/', $pw)) return [false, 'Password must include at least one digit.'];
    if (!preg_match('/[!@#\$%\^&\*()_+\-=[\]{};:\"\\|,.<>\/?]/', $pw)) return [false, 'Password must include at least one special character.'];

    // simple common password blacklist (keep short for performance)
    $common = [
        'password','123456','12345678','qwerty','abc123','letmein','admin','welcome','password1','123456789'
    ];
    foreach ($common as $c) {
        if (strcasecmp($pw, $c) === 0) return [false, 'Password is too common. Please choose a less guessable password.'];
    }

    return [true, ''];
}

function save_school_profile($user_id, $name, $address, $logoPath) {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE schools SET school_name=?, address=?, logo_path=? WHERE user_id=?');
    return $stmt->execute([$name, $address, $logoPath, $user_id]);
}

function save_sponsor_profile($user_id, $org, $contact, $logoPath) {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE sponsors SET organization=?, contact_info=?, logo_path=? WHERE user_id=?');
    return $stmt->execute([$org, $contact, $logoPath, $user_id]);
}

/**
 * Password reset token helpers.
 * Creates a simple table on first use: password_resets (id, user_id, token, expires_at, used, created_at)
 */
function ensure_password_resets_table() {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(128) NOT NULL,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (token(64)),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

function create_password_reset_token($user_id, $ttl_minutes = 60) {
    ensure_password_resets_table();
    $pdo = db();
    $token = bin2hex(random_bytes(24));
    $expires = date('Y-m-d H:i:s', time() + ($ttl_minutes * 60));
    $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)');
    $stmt->execute([$user_id, $token, $expires]);
    return $token;
}

function get_password_reset_by_token($token) {
    ensure_password_resets_table();
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token=? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;
    if ((int)$row['used'] === 1) return null;
    if (strtotime($row['expires_at']) < time()) return null;
    return $row;
}

function mark_password_reset_used($token) {
    ensure_password_resets_table();
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE password_resets SET used=1 WHERE token=?');
    return $stmt->execute([$token]);
}

/**
 * Rate limiting for password reset attempts.
 * Tracks per (IP, email) to prevent abuse.
 */
function ensure_rate_limit_table() {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        email VARCHAR(255) NOT NULL,
        attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (ip_address, email),
        INDEX (attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

function check_reset_rate_limit($email, $limit = 5, $window_minutes = 60) {
    ensure_rate_limit_table();
    $pdo = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $since = date('Y-m-d H:i:s', time() - ($window_minutes * 60));
    
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM password_reset_attempts WHERE ip_address=? AND email=? AND attempted_at>?');
    $stmt->execute([$ip, $email, $since]);
    $count = (int)$stmt->fetchColumn();
    
    return $count < $limit;
}

function record_reset_attempt($email) {
    ensure_rate_limit_table();
    $pdo = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare('INSERT INTO password_reset_attempts (ip_address, email) VALUES (?,?)');
    $stmt->execute([$ip, $email]);
}
