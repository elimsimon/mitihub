<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');
header('Content-Type: application/json');

try {
    $u = current_user();
    $school_id = (int) (school_get_profile_id($u['id'] ?? null) ?: ($u['id'] ?? 0));
    $pdo = db();
    // Try to compute from available tables; fallback zeros
    $planted = 0; $adopted = 0; $survival = 0.0; $points = 0; $badges = [];
    try { $planted = (int)$pdo->query('SELECT COUNT(*) FROM trees WHERE school_id='.(int)$school_id)->fetchColumn(); } catch (Throwable $e) {}
    try { $adopted = (int)$pdo->query('SELECT COUNT(*) FROM tree_adoptions WHERE adopted_by_school_id='.(int)$school_id)->fetchColumn(); } catch (Throwable $e) {}
    try { $survival = (float)$pdo->query('SELECT AVG(survival_rate) FROM trees WHERE school_id='.(int)$school_id)->fetchColumn(); if (!$survival) $survival = 0.0; } catch (Throwable $e) {}
    try { $points = (int)$pdo->query('SELECT COALESCE(SUM(points),0) FROM points WHERE school_id='.(int)$school_id)->fetchColumn(); } catch (Throwable $e) {}
    try {
        $badges = $pdo->query('SELECT b.name FROM badge_awards ba JOIN badges b ON ba.badge_id=b.id WHERE ba.school_id='.(int)$school_id.' ORDER BY ba.awarded_at DESC LIMIT 10')->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {}

    // Compute survivals and health updates (safe fallbacks)
    $six_month_survivors = 0; $one_year_survivors = 0; $health_updates = 0;
    try {
      $six_month_survivors = (int)$pdo->query("SELECT COUNT(*) FROM trees WHERE school_id={$school_id} AND date_planted <= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND (survival_rate IS NULL OR survival_rate >= 1)")->fetchColumn();
    } catch (Throwable $e) {}
    try {
      $one_year_survivors = (int)$pdo->query("SELECT COUNT(*) FROM trees WHERE school_id={$school_id} AND date_planted <= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND (survival_rate IS NULL OR survival_rate >= 1)")->fetchColumn();
    } catch (Throwable $e) {}
    try {
      $health_updates = (int)$pdo->query("SELECT COUNT(*) FROM tree_journals j JOIN trees t ON t.id=j.tree_id WHERE t.school_id={$school_id} AND (j.event_type='health' OR j.health_status IS NOT NULL)")->fetchColumn();
    } catch (Throwable $e) {}

    $points_breakdown = [
      'planting' => $planted * 10,
      'adoption' => $adopted * 5,
      'survival_6m' => $six_month_survivors * 15,
      'survival_1y' => $one_year_survivors * 25,
      'health_updates' => $health_updates * 2,
    ];
    $points_total = array_sum($points_breakdown);

    $avg_survival = $survival;
    $computed_badges = [];
    if ($planted >= 10) $computed_badges[] = 'Green Starter';
    if ($planted >= 100) $computed_badges[] = 'Top Planter';
    if ($adopted >= 20) $computed_badges[] = 'Adoption Hero';
    if ($avg_survival >= 80) $computed_badges[] = 'Sustainability Star';

    $planter_level = null;
    if ($planted >= 100) $planter_level = 'Gold Planter';
    elseif ($planted >= 50) $planter_level = 'Silver Planter';
    elseif ($planted >= 10) $planter_level = 'Bronze Planter';

    echo json_encode([
        'trees_planted' => $planted,
        'trees_adopted' => $adopted,
        'survival_rate' => round($avg_survival, 1),
        'points' => $points_total,
        'points_breakdown' => $points_breakdown,
        'badges' => array_values(array_unique(array_merge($badges, $computed_badges))),
        'level' => $planter_level,
        'survivors_6m' => $six_month_survivors,
        'survivors_1y' => $one_year_survivors,
        'health_updates' => $health_updates,
        'rules' => [
          'planting' => 10,
          'adoption' => 5,
          'survival_6m' => 15,
          'survival_1y' => 25,
          'health_update' => 2,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'trees_planted' => 0,
        'trees_adopted' => 0,
        'survival_rate' => 0.0,
        'points' => 0,
        'points_breakdown' => ['planting'=>0,'adoption'=>0,'survival_6m'=>0,'survival_1y'=>0,'health_updates'=>0],
        'badges' => [],
        'level' => null,
        'survivors_6m' => 0,
        'survivors_1y' => 0,
        'health_updates' => 0,
        'rules' => [
          'planting' => 10,
          'adoption' => 5,
          'survival_6m' => 15,
          'survival_1y' => 25,
          'health_update' => 2,
        ],
    ]);
}
