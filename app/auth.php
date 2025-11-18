<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

function auth_user() {
    start_session();
    return $_SESSION['user'] ?? null;
}

function require_guest() {
    if (auth_user()) redirect(base_url('public/dashboard.php'));
}

function require_auth() {
    if (!auth_user()) redirect(base_url('public/login.php'));
}

function require_role($roles) {
    require_auth();
    $user = auth_user();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function login($email, $password) {
    // $email can be an email address or username; normalize input
    $identifier = trim((string)$email);
    $password = (string)$password;

    if ($identifier === '' || $password === '') {
        return false;
    }

    try {
        $pdo = db();
        // Try to find user by email or name (username). Select only necessary columns.
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? OR name = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // No user found
            return false;
        }

        // Optionally check if account is active
        if (isset($user['status']) && $user['status'] !== 'active') {
            // Treat non-active account as authentication failure.
            return false;
        }

        if (!isset($user['password_hash']) || $user['password_hash'] === '') {
            // Bad/missing password hash in DB
            error_log("auth: user {$user['id']} has no password_hash");
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            // Wrong password
            return false;
        }

        // Password ok — start session and store minimal user info
        start_session();
        // Regenerate session id on login to mitigate fixation
        if (function_exists('session_regenerate_id')) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return true;
    } catch (Exception $e) {
        // Log error server-side for debugging without leaking to users
        error_log('Login error: ' . $e->getMessage());
        return false;
    }
}

// Consolidated auth functions from includes/auth.php
function start_session_if_needed() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in() {
    start_session_if_needed();
    return !empty($_SESSION['user']);
}

function current_user() {
    start_session_if_needed();
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('index.php'));
        exit;
    }
}

function login_user($email, $password) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        if (($user['status'] ?? 'active') !== 'active') {
            // prevent login
            return 'pending';
        }
        start_session_if_needed();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'setup_complete' => (int)($user['setup_complete'] ?? 1),
        ];
        return true;
    }
    return false;
}

function logout_user() {
    start_session_if_needed();
    $_SESSION = [];
    session_destroy();
}

function require_role_mod($roles) {
    require_login();
    $user = current_user();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function logout() {
    start_session();
    $_SESSION = [];
    session_destroy();
}

function user_by_id($id) {
    $stmt = db()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function users_all() {
    return db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();
}

function user_create($name, $email, $password, $role) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)');
    $stmt->execute([$name, $email, $hash, $role]);
    return db()->lastInsertId();
}

function user_delete($id) {
    $stmt = db()->prepare('DELETE FROM users WHERE id=?');
    return $stmt->execute([$id]);
}

function sponsor_profile($user_id) {
    $stmt = db()->prepare('SELECT * FROM sponsors WHERE user_id=?');
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function school_profile($user_id) {
    $stmt = db()->prepare('SELECT * FROM schools WHERE user_id=?');
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function sponsorships_for_sponsor($sponsor_id) {
    $stmt = db()->prepare('SELECT s.*, sc.school_name FROM sponsorships s JOIN schools sc ON s.school_id=sc.id WHERE s.sponsor_id=? ORDER BY s.id DESC');
    $stmt->execute([$sponsor_id]);
    return $stmt->fetchAll();
}

function sponsorships_for_school($school_id) {
    $stmt = db()->prepare('SELECT s.*, sp.organization FROM sponsorships s JOIN sponsors sp ON s.sponsor_id=sp.id WHERE s.school_id=? ORDER BY s.id DESC');
    $stmt->execute([$school_id]);
    return $stmt->fetchAll();
}

function sponsorship_create($sponsor_id, $school_id, $amount) {
    $stmt = db()->prepare('INSERT INTO sponsorships (sponsor_id, school_id, amount, status) VALUES (?,?,?,"pending")');
    $stmt->execute([$sponsor_id, $school_id, $amount]);
    return db()->lastInsertId();
}

function sponsorship_update_status($id, $status) {
    $stmt = db()->prepare('UPDATE sponsorships SET status=? WHERE id=?');
    return $stmt->execute([$status, $id]);
}
