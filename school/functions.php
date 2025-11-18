<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/config.php';

function school_get_profile_id($user_id) {
    $stmt = db()->prepare('SELECT id FROM schools WHERE user_id=? LIMIT 1');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return $row['id'] ?? null;
}

function school_list_sponsorships($user_id) {
    $school_id = school_get_profile_id($user_id);
    if (!$school_id) return [];
    $stmt = db()->prepare('SELECT s.*, sp.organization FROM sponsorships s JOIN sponsors sp ON s.sponsor_id=sp.id WHERE s.school_id=? ORDER BY s.id DESC');
    $stmt->execute([$school_id]);
    return $stmt->fetchAll();
}
