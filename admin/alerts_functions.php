<?php
/**
 * Admin Alerts & Monetization Functions
 * Handles alerts, audit logging, sponsorship packages, licensing, premium features, and compliance
 */

require_once __DIR__ . '/../app/config.php';

// ==================== ALERTS FUNCTIONS ====================

/**
 * Get pending alerts for admin dashboard
 */
function get_pending_alerts($limit = 10) {
    try {
        $stmt = db()->prepare('SELECT * FROM alerts WHERE is_read=0 AND is_dismissed=0 ORDER BY severity DESC, created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        // Table doesn't exist yet - return empty array during development
        return [];
    }
}

/**
 * Get alert summary counts by type
 */
function get_alert_counts() {
    try {
        $stmt = db()->query('SELECT alert_type, severity, COUNT(*) as count FROM alerts WHERE is_read=0 AND is_dismissed=0 GROUP BY alert_type, severity');
        $results = $stmt->fetchAll();
    } catch (Exception $e) {
        // Table doesn't exist yet - return zeros
        $results = [];
    }
    $counts = [
        'pending_approval' => 0,
        'flagged_issue' => 0,
        'revenue' => 0,
        'compliance' => 0,
        'critical' => 0,
        'warning' => 0,
        'total' => 0
    ];
    foreach ($results as $row) {
        $counts[$row['alert_type']] += $row['count'];
        $counts[$row['severity']] += $row['count'];
        $counts['total'] += $row['count'];
    }
    return $counts;
}

/**
 * Create a new alert
 */
function create_alert($alert_type, $title, $message, $severity = 'info', $related_id = null, $related_type = null) {
    $stmt = db()->prepare('INSERT INTO alerts (alert_type, title, message, severity, related_id, related_type) VALUES (?, ?, ?, ?, ?, ?)');
    return $stmt->execute([$alert_type, $title, $message, $severity, $related_id, $related_type]);
}

/**
 * Mark alert as read
 */
function mark_alert_read($alert_id) {
    $stmt = db()->prepare('UPDATE alerts SET is_read=1, read_at=NOW() WHERE id=?');
    return $stmt->execute([$alert_id]);
}

/**
 * Dismiss alert
 */
function dismiss_alert($alert_id) {
    $stmt = db()->prepare('UPDATE alerts SET is_dismissed=1, dismissed_at=NOW() WHERE id=?');
    return $stmt->execute([$alert_id]);
}

/**
 * Get pending user approvals (schools and sponsors)
 */
function get_pending_user_approvals() {
    try {
        $stmt = db()->query("SELECT id, name, email, role, status, created_at FROM users WHERE status='pending_approval' ORDER BY created_at ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get pending tree log approvals (with compliance flags)
 */
function get_pending_tree_logs() {
    $stmt = db()->query("SELECT tl.id, tl.user_id, tl.latitude, tl.longitude, tl.tree_count, tl.status, tl.created_at, u.name, u.email FROM tree_logs tl JOIN users u ON tl.user_id=u.id WHERE tl.status='pending' ORDER BY tl.created_at ASC");
    return $stmt->fetchAll();
}

/**
 * Get flagged compliance issues
 */
function get_flagged_issues($limit = 20) {
    try {
        $stmt = db()->prepare('SELECT * FROM compliance_flags WHERE is_resolved=0 ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get recent revenue alerts (new sponsorships)
 */
function get_revenue_alerts($days = 7) {
    try {
        $stmt = db()->prepare('SELECT s.id, s.sponsor_id, s.school_id, s.amount, s.status, s.created_at, sp.organization, sc.school_name FROM sponsorships s JOIN sponsors sp ON s.sponsor_id=sp.id JOIN schools sc ON s.school_id=sc.id WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) ORDER BY s.created_at DESC');
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Create compliance flag for issues
 */
function create_compliance_flag($flag_type, $tree_log_id, $description) {
    $stmt = db()->prepare('INSERT INTO compliance_flags (flag_type, tree_log_id, description) VALUES (?, ?, ?)');
    return $stmt->execute([$flag_type, $tree_log_id, $description]);
}

// ==================== AUDIT LOGGING FUNCTIONS ====================

/**
 * Log an admin action to audit trail
 */
function log_admin_action($admin_id, $action, $entity_type, $entity_id, $details, $old_values = null, $new_values = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = db()->prepare('INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, old_values, new_values, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    return $stmt->execute([
        $admin_id,
        $action,
        $entity_type,
        $entity_id,
        $details,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $ip
    ]);
}

/**
 * Get audit logs with filters
 */
function get_audit_logs($limit = 100, $admin_id = null, $action = null, $entity_type = null) {
    $sql = 'SELECT al.*, u.name as admin_name FROM audit_logs al JOIN users u ON al.admin_id=u.id WHERE 1=1';
    $params = [];
    if ($admin_id) {
        $sql .= ' AND al.admin_id=?';
        $params[] = $admin_id;
    }
    if ($action) {
        $sql .= ' AND al.action=?';
        $params[] = $action;
    }
    if ($entity_type) {
        $sql .= ' AND al.entity_type=?';
        $params[] = $entity_type;
    }
    $sql .= ' ORDER BY al.created_at DESC LIMIT ?';
    $params[] = $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ==================== MONETIZATION - SPONSORSHIP PACKAGES ====================

/**
 * Get all sponsorship packages
 */
function get_sponsorship_packages($only_active = true) {
    $sql = 'SELECT * FROM sponsorship_packages';
    if ($only_active) {
        $sql .= ' WHERE is_active=1';
    }
    $sql .= ' ORDER BY price_ksh ASC';
    return db()->query($sql)->fetchAll();
}

/**
 * Get sponsorship package by ID
 */
function get_sponsorship_package($id) {
    $stmt = db()->prepare('SELECT * FROM sponsorship_packages WHERE id=?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Create or update sponsorship package
 */
function save_sponsorship_package($data) {
    if (!empty($data['id'])) {
        // Update
        $stmt = db()->prepare('UPDATE sponsorship_packages SET name=?, description=?, price_ksh=?, max_trees=?, max_duration_months=?, includes_branding=?, includes_reporting=?, includes_sms_subsidy=?, is_active=? WHERE id=?');
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price_ksh'],
            $data['max_trees'],
            $data['max_duration_months'],
            $data['includes_branding'] ? 1 : 0,
            $data['includes_reporting'] ? 1 : 0,
            $data['includes_sms_subsidy'] ? 1 : 0,
            $data['is_active'] ? 1 : 0,
            $data['id']
        ]);
    } else {
        // Create
        $stmt = db()->prepare('INSERT INTO sponsorship_packages (name, description, price_ksh, max_trees, max_duration_months, includes_branding, includes_reporting, includes_sms_subsidy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price_ksh'],
            $data['max_trees'],
            $data['max_duration_months'],
            $data['includes_branding'] ? 1 : 0,
            $data['includes_reporting'] ? 1 : 0,
            $data['includes_sms_subsidy'] ? 1 : 0
        ]);
    }
}

// ==================== MONETIZATION - NGO LICENSING ====================

/**
 * Get all NGO licensing tiers
 */
function get_ngo_licenses($only_active = true) {
    $sql = 'SELECT * FROM ngo_licenses';
    if ($only_active) {
        $sql .= ' WHERE is_active=1';
    }
    $sql .= ' ORDER BY price_ksh_annual ASC';
    return db()->query($sql)->fetchAll();
}

/**
 * Save NGO license tier
 */
function save_ngo_license($data) {
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE ngo_licenses SET tier_name=?, description=?, price_ksh_annual=?, max_schools=?, max_api_calls_monthly=?, includes_analytics=?, includes_sms_gateway=?, includes_data_export=?, includes_custom_branding=?, is_active=? WHERE id=?');
        return $stmt->execute([
            $data['tier_name'],
            $data['description'],
            $data['price_ksh_annual'],
            $data['max_schools'],
            $data['max_api_calls_monthly'],
            $data['includes_analytics'] ? 1 : 0,
            $data['includes_sms_gateway'] ? 1 : 0,
            $data['includes_data_export'] ? 1 : 0,
            $data['includes_custom_branding'] ? 1 : 0,
            $data['is_active'] ? 1 : 0,
            $data['id']
        ]);
    } else {
        $stmt = db()->prepare('INSERT INTO ngo_licenses (tier_name, description, price_ksh_annual, max_schools, max_api_calls_monthly, includes_analytics, includes_sms_gateway, includes_data_export, includes_custom_branding) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            $data['tier_name'],
            $data['description'],
            $data['price_ksh_annual'],
            $data['max_schools'],
            $data['max_api_calls_monthly'],
            $data['includes_analytics'] ? 1 : 0,
            $data['includes_sms_gateway'] ? 1 : 0,
            $data['includes_data_export'] ? 1 : 0,
            $data['includes_custom_branding'] ? 1 : 0
        ]);
    }
}

// ==================== MONETIZATION - SMS/USSD SETTINGS ====================

/**
 * Get SMS/USSD setting
 */
function get_sms_setting($setting_key, $default = null) {
    $stmt = db()->prepare('SELECT setting_value FROM sms_settings WHERE setting_key=?');
    $stmt->execute([$setting_key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

/**
 * Get all SMS settings
 */
function get_all_sms_settings() {
    return db()->query('SELECT * FROM sms_settings ORDER BY setting_key ASC')->fetchAll();
}

/**
 * Update SMS/USSD setting
 */
function update_sms_setting($setting_key, $setting_value) {
    $stmt = db()->prepare('UPDATE sms_settings SET setting_value=? WHERE setting_key=?');
    return $stmt->execute([$setting_value, $setting_key]);
}

// ==================== MONETIZATION - PREMIUM FEATURES ====================

/**
 * Get all premium features
 */
function get_premium_features() {
    return db()->query('SELECT * FROM premium_features ORDER BY feature_name ASC')->fetchAll();
}

/**
 * Enable/disable premium feature
 */
function toggle_premium_feature($feature_id, $is_enabled) {
    $stmt = db()->prepare('UPDATE premium_features SET is_enabled=? WHERE id=?');
    return $stmt->execute([$is_enabled ? 1 : 0, $feature_id]);
}

/**
 * Update premium feature price
 */
function update_premium_feature_price($feature_id, $price_ksh) {
    $stmt = db()->prepare('UPDATE premium_features SET price_ksh=? WHERE id=?');
    return $stmt->execute([$price_ksh, $feature_id]);
}

// ==================== DATA LICENSING & RESEARCH REQUESTS ====================

/**
 * Get pending research requests
 */
function get_pending_research_requests() {
    $stmt = db()->query("SELECT * FROM research_requests WHERE is_approved=0 ORDER BY created_at ASC");
    return $stmt->fetchAll();
}

/**
 * Approve research request
 */
function approve_research_request($request_id, $admin_id, $license_price_ksh) {
    $stmt = db()->prepare('UPDATE research_requests SET is_approved=1, approved_by=?, license_price_ksh=?, approved_at=NOW() WHERE id=?');
    return $stmt->execute([$admin_id, $license_price_ksh, $request_id]);
}

/**
 * Deny research request
 */
function deny_research_request($request_id) {
    $stmt = db()->prepare('UPDATE research_requests SET is_approved=0 WHERE id=?');
    return $stmt->execute([$request_id]);
}

// ==================== API ACCESS MANAGEMENT ====================

/**
 * Get API access record for user
 */
function get_user_api_access($user_id) {
    $stmt = db()->prepare('SELECT * FROM api_access WHERE user_id=?');
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Create API access for user
 */
function create_user_api_access($user_id, $tier = 'free') {
    $api_key = bin2hex(random_bytes(32));
    $stmt = db()->prepare('INSERT INTO api_access (user_id, api_tier, api_key) VALUES (?, ?, ?)');
    return $stmt->execute([$user_id, $tier, $api_key]);
}

/**
 * Upgrade user API tier
 */
function upgrade_api_tier($user_id, $new_tier, $rate_limit = null) {
    $stmt = db()->prepare('UPDATE api_access SET api_tier=?, rate_limit_calls_monthly=? WHERE user_id=?');
    return $stmt->execute([$new_tier, $rate_limit, $user_id]);
}

/**
 * Get all API access records (admin view)
 */
function get_all_api_access() {
    $stmt = db()->query('SELECT aa.*, u.name, u.email FROM api_access aa JOIN users u ON aa.user_id=u.id ORDER BY aa.api_tier, u.name');
    return $stmt->fetchAll();
}

// ==================== HELPER FUNCTIONS ====================

/**
 * Generate alert for pending user approval
 */
function trigger_user_approval_alert($user_id, $user_email, $user_role) {
    $title = ucfirst($user_role) . ' Pending Approval';
    $message = "New {$user_role} ({$user_email}) awaiting approval";
    return create_alert('pending_approval', $title, $message, 'warning', $user_id, 'user');
}

/**
 * Generate alert for compliance issue
 */
function trigger_compliance_alert($issue_type, $tree_log_id, $message) {
    $severity_map = [
        'duplicate_log' => 'critical',
        'gps_mismatch' => 'warning',
        'missing_photo' => 'warning',
        'mrv_incomplete' => 'critical'
    ];
    return create_alert('compliance', ucfirst(str_replace('_', ' ', $issue_type)), $message, $severity_map[$issue_type] ?? 'warning', $tree_log_id, 'tree_log');
}

/**
 * Generate alert for revenue/sponsorship
 */
function trigger_revenue_alert($sponsorship_id, $amount_ksh, $sponsor_name) {
    $title = 'New Sponsorship';
    $message = "New sponsorship worth KSH {$amount_ksh} from {$sponsor_name}";
    return create_alert('revenue', $title, $message, 'info', $sponsorship_id, 'sponsorship');
}

?>
