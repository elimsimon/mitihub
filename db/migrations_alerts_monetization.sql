-- Alerts & Monetization System Database Schema
-- Run this migration after setting up the base mitihub.sql schema

-- 1. Alerts table - stores system alerts (pending approvals, flagged issues, revenue, compliance)
CREATE TABLE IF NOT EXISTS alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type ENUM('pending_approval', 'flagged_issue', 'revenue', 'compliance') NOT NULL,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    related_id INT COMMENT 'ID of related entity (user, log, sponsorship, etc)',
    related_type VARCHAR(50) COMMENT 'Type of entity (user, log, sponsorship, tree_log)',
    is_read BOOLEAN DEFAULT FALSE,
    is_dismissed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    dismissed_at TIMESTAMP NULL,
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_is_read (is_read),
    INDEX idx_related (related_type, related_id),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Audit logs - track all admin actions for compliance & governance
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL COMMENT 'e.g., approve_user, deny_user, update_monetization',
    entity_type VARCHAR(50) NOT NULL COMMENT 'e.g., user, sponsorship, sms_setting',
    entity_id INT,
    old_values JSON COMMENT 'Previous state (for updates)',
    new_values JSON COMMENT 'New state (for updates)',
    details TEXT COMMENT 'Human-readable description',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Sponsorship packages - define monetization tiers for corporate sponsors
CREATE TABLE IF NOT EXISTS sponsorship_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT 'Bronze, Silver, Gold, etc',
    description TEXT,
    price_ksh INT NOT NULL COMMENT 'Price in Kenyan Shillings',
    max_trees INT COMMENT 'Max trees sponsor can fund per package',
    max_duration_months INT COMMENT 'Duration of sponsorship',
    includes_branding BOOLEAN DEFAULT FALSE COMMENT 'Sponsor logo & announcements',
    includes_reporting BOOLEAN DEFAULT FALSE COMMENT 'Monthly impact reports',
    includes_sms_subsidy BOOLEAN DEFAULT FALSE COMMENT 'Sponsor subsidizes SMS',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_price (price_ksh)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. NGO & Government licensing tiers
CREATE TABLE IF NOT EXISTS ngo_licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tier_name VARCHAR(50) NOT NULL COMMENT 'Basic, Pro, Enterprise',
    description TEXT,
    price_ksh_annual INT NOT NULL COMMENT 'Annual cost in KSH',
    max_schools INT COMMENT 'Max schools per tier',
    max_api_calls_monthly INT COMMENT 'API rate limit',
    includes_analytics BOOLEAN DEFAULT FALSE,
    includes_sms_gateway BOOLEAN DEFAULT FALSE,
    includes_data_export BOOLEAN DEFAULT FALSE,
    includes_custom_branding BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tier (tier_name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. SMS/USSD settings - monetization for messaging
CREATE TABLE IF NOT EXISTS sms_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL COMMENT 'e.g., sms_per_message_cost_ksh',
    setting_value VARCHAR(255),
    description TEXT,
    data_type ENUM('number', 'string', 'boolean', 'json') DEFAULT 'string',
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Premium features - in-app purchases & add-ons
CREATE TABLE IF NOT EXISTS premium_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    feature_name VARCHAR(100) NOT NULL COMMENT 'Digital certificates, SMS alerts, etc',
    description TEXT,
    price_ksh INT,
    is_enabled BOOLEAN DEFAULT FALSE COMMENT 'Admin can enable/disable',
    requires_approval BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_enabled (is_enabled),
    INDEX idx_feature_name (feature_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Research & data licensing requests
CREATE TABLE IF NOT EXISTS research_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    requester_name VARCHAR(255) NOT NULL,
    requester_email VARCHAR(255),
    organization VARCHAR(255),
    research_purpose TEXT,
    requested_data_type VARCHAR(100) COMMENT 'e.g., tree_logs, sponsorship_data',
    is_approved BOOLEAN DEFAULT FALSE,
    approval_date TIMESTAMP NULL,
    approved_by INT COMMENT 'Admin ID who approved',
    license_price_ksh INT COMMENT 'If approved, cost in KSH',
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_is_approved (is_approved),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. API access management - control API tier access
CREATE TABLE IF NOT EXISTS api_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    api_tier ENUM('free', 'paid', 'enterprise') DEFAULT 'free',
    api_key VARCHAR(255) UNIQUE COMMENT 'Generated API key',
    is_active BOOLEAN DEFAULT TRUE,
    rate_limit_calls_monthly INT COMMENT 'Monthly call limit (NULL = unlimited)',
    calls_this_month INT DEFAULT 0,
    last_reset TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_api (user_id),
    INDEX idx_api_tier (api_tier),
    INDEX idx_is_active (is_active),
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Compliance flags - track issues like duplicate logs, GPS mismatch, missing photos
CREATE TABLE IF NOT EXISTS compliance_flags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    flag_type ENUM('duplicate_log', 'gps_mismatch', 'missing_photo', 'mrv_incomplete') NOT NULL,
    tree_log_id INT COMMENT 'Related tree log if applicable',
    description TEXT,
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_at TIMESTAMP NULL,
    resolved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tree_log_id) REFERENCES tree_logs(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_flag_type (flag_type),
    INDEX idx_is_resolved (is_resolved),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Insert default monetization settings
INSERT INTO sms_settings (setting_key, setting_value, description, data_type) VALUES
('sms_per_message_cost_ksh', '2.5', 'Cost per SMS message in KSH', 'number'),
('ussd_per_session_cost_ksh', '1.5', 'Cost per USSD session in KSH', 'number'),
('sponsor_sms_subsidy_percent', '50', 'Sponsor subsidy percentage for SMS (0-100)', 'number'),
('carbon_credit_price_per_tree', '500', 'Price per tree for carbon credits (future)', 'number'),
('min_research_data_price_ksh', '2000', 'Minimum research dataset price in KSH', 'number'),
('max_research_data_price_ksh', '10000', 'Maximum research dataset price in KSH', 'number')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- 11. Insert default sponsorship packages
INSERT INTO sponsorship_packages (name, description, price_ksh, max_trees, max_duration_months, includes_branding, includes_reporting, includes_sms_subsidy) VALUES
('Bronze', 'Basic community tree planting', 5000, 50, 6, FALSE, FALSE, FALSE),
('Silver', 'Corporate tree planting with reporting', 15000, 200, 12, TRUE, TRUE, FALSE),
('Gold', 'Premium sponsorship with SMS subsidy', 50000, 500, 12, TRUE, TRUE, TRUE)
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- 12. Insert default NGO licensing tiers
INSERT INTO ngo_licenses (tier_name, description, price_ksh_annual, max_schools, max_api_calls_monthly, includes_analytics, includes_sms_gateway, includes_data_export, includes_custom_branding) VALUES
('Basic', 'Single NGO operation', 5000, 10, 10000, TRUE, FALSE, FALSE, FALSE),
('Pro', 'Multi-site NGO with SMS', 25000, 100, 100000, TRUE, TRUE, TRUE, FALSE),
('Enterprise', 'County government or large NGO', 100000, 1000, 1000000, TRUE, TRUE, TRUE, TRUE)
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- 13. Insert default premium features
INSERT INTO premium_features (feature_name, description, price_ksh, is_enabled, requires_approval) VALUES
('Digital Certificates', 'Digitally signed tree planting certificates', 500, FALSE, TRUE),
('Personalized SMS Alerts', 'Custom SMS notifications for tree care', 200, FALSE, TRUE),
('Advanced Analytics', 'Detailed impact reporting and analytics', 1000, FALSE, FALSE),
('Custom Branding', 'Sponsor logo and custom branding in reports', 0, TRUE, FALSE)
ON DUPLICATE KEY UPDATE description=VALUES(description);
