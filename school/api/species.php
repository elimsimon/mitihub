<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role_mod('school');
header('Content-Type: application/json');

try {
    $pdo = db();
    // If species table exists
    $rows = [];
    try {
        $rows = $pdo->query('SELECT id, name FROM species ORDER BY name')->fetchAll();
    } catch (Throwable $e) {
        // fallback defaults
        $rows = [
            ['id'=>1,'name'=>'Grevillea'],
            ['id'=>2,'name'=>'Mango'],
            ['id'=>3,'name'=>'Avocado'],
            ['id'=>4,'name'=>'Cypress'],
        ];
    }
    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'failed']);
}
