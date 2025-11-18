<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/config.php';

function sponsor_get_profile_id($user_id) {
    $stmt = db()->prepare('SELECT id FROM sponsors WHERE user_id=? LIMIT 1');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    return $row['id'] ?? null;
}

function sponsor_list_schools() {
    return db()->query('SELECT id, school_name FROM schools ORDER BY school_name ASC')->fetchAll();
}

function sponsor_list_sponsorships($user_id) {
    $sponsor_id = sponsor_get_profile_id($user_id);
    if (!$sponsor_id) return [];
    $stmt = db()->prepare('SELECT s.*, sc.school_name FROM sponsorships s JOIN schools sc ON s.school_id=sc.id WHERE s.sponsor_id=? ORDER BY s.id DESC');
    $stmt->execute([$sponsor_id]);
    return $stmt->fetchAll();
}

function sponsor_create_sponsorship($user_id, $school_id, $amount) {
    $sponsor_id = sponsor_get_profile_id($user_id);
    if (!$sponsor_id) return false;
    $stmt = db()->prepare('INSERT INTO sponsorships (sponsor_id, school_id, amount, status) VALUES (?,?,?,\'pending\')');
    return $stmt->execute([$sponsor_id, $school_id, $amount]);
}
