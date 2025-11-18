<?php
require_once dirname(__DIR__) . '/bootstrap.php';

function admin_list_users() {
    $pdo = db();
    return $pdo->query('SELECT id, email, name, role FROM users ORDER BY id DESC')->fetchAll();
}
