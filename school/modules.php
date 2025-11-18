<?php
/**
 * School modules: centralizes school-only logic and placeholders for API integration.
 * Scope all operations to the current school_id and enforce role=school.
 */
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';

/**
 * Resolve and guard school_id for the current user. Exits with 403 JSON if invalid when $fatal=true.
 */
function school_guard_school_id(bool $fatal = true) {
    require_role_mod('school');
    $user = current_user();
    $school_id = school_get_profile_id($user['id'] ?? null);
    if (!$school_id && $fatal) {
        if (php_sapi_name() !== 'cli') {
            http_response_code(403);
            header('Content-Type: application/json');
        }
        echo json_encode(['error' => 'Forbidden: school not found for user']);
        exit;
    }
    return $school_id;
}

/**
 * Placeholder: aggregate stats for a school.
 */
function school_stats_placeholder(int $school_id): array {
    return [
        'trees_planted' => 0,
        'trees_adopted' => 0,
        'survival_rate' => 0.0,
        'points' => 0,
        'badges' => [],
    ];
}

/**
 * Placeholder: list trees by type for a school.
 * type: planted|adopted
 */
function school_list_trees_placeholder(int $school_id, string $type = 'planted', int $limit = 25, int $offset = 0): array {
    return [
        'items' => [],
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => 0,
        ],
    ];
}

/**
 * Placeholder: plant a tree (no-op).
 */
function school_plant_tree_placeholder(int $school_id, int $user_id, array $data): array {
    return [
        'ok' => false,
        'message' => 'Planting endpoint not yet implemented',
    ];
}

/**
 * Placeholder: adopt a tree (no-op).
 */
function school_adopt_tree_placeholder(int $school_id, int $user_id, int $tree_id): array {
    return [
        'ok' => false,
        'message' => 'Adoption endpoint not yet implemented',
    ];
}

/**
 * Placeholder: add health update (no-op).
 */
function school_health_update_placeholder(int $school_id, int $user_id, int $tree_id, string $status, string $notes = ''): array {
    return [
        'ok' => false,
        'message' => 'Health update endpoint not yet implemented',
    ];
}

/**
 * Placeholder: GeoJSON of school trees.
 */
function school_map_geojson_placeholder(int $school_id): array {
    return [
        'type' => 'FeatureCollection',
        'features' => [],
    ];
}

/**
 * Placeholder: leaderboard summary for a school.
 */
function school_leaderboard_summary_placeholder(int $school_id): array {
    return [
        'rank' => null,
        'total_schools' => 0,
        'top' => [],
        'metrics' => [
            'planted' => 0,
            'adopted' => 0,
            'survival_rate' => 0.0,
            'badges' => 0,
            'points' => 0,
        ],
    ];
}

/**
 * Placeholder: reports generation meta.
 */
function school_reports_generate_placeholder(int $school_id, string $type, string $format = 'csv', array $filters = []): array {
    return [
        'ok' => false,
        'message' => 'Report generation not yet implemented',
        'type' => $type,
        'format' => $format,
    ];
}

/**
 * Placeholder: school profile update.
 */
function school_profile_update_placeholder(int $school_id, array $payload): array {
    return [
        'ok' => false,
        'message' => 'Profile update not yet implemented',
    ];
}

// Convenience JSON responder for endpoints that include this module directly
function school_json($data, int $code = 200): void {
    if (php_sapi_name() !== 'cli') {
        http_response_code($code);
        header('Content-Type: application/json');
    }
    echo json_encode($data);
}
