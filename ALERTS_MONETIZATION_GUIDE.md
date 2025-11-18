# Admin Alerts & Monetization System

## Overview

MitiHub now includes a comprehensive **Alerts & Governance** system and **Monetization Controls** for administrators to manage the platform as an ecosystem governor. This system ensures data integrity, controls revenue streams, and provides compliance tracking.

## Features Implemented

### 1. **Alerts System** 🚨
- **Pending Approvals**: Track schools, sponsors, and logs awaiting admin approval
- **Flagged Issues**: Data quality issues (duplicate logs, GPS mismatch, missing photos, MRV incomplete)
- **Revenue Alerts**: New sponsorship campaigns with real-time KSH amounts
- **Compliance Alerts**: Track logs missing MRV (Monitoring, Reporting, Verification) data

#### Alert Types
- `pending_approval` - New users or logs awaiting approval
- `flagged_issue` - Data integrity problems
- `revenue` - Monetization events (new sponsorships)
- `compliance` - Governance & compliance issues

#### Severity Levels
- `info` - Informational
- `warning` - Requires attention
- `critical` - Urgent action needed

### 2. **Monetization Controls** 💰

#### Sponsorship Packages (Bronze/Silver/Gold)
Define tiered sponsorship levels with:
- Fixed pricing (KSH)
- Max trees per package
- Duration (months)
- Optional branding & reporting
- SMS subsidy control

#### NGO & Government Licensing (SaaS)
Sell Admin Console to NGOs and county governments:
- **Basic**: Single NGO, limited schools & API calls
- **Pro**: Multi-site with SMS gateway & data export
- **Enterprise**: Unlimited schools, custom branding, full API access

#### SMS/USSD Monetization
Configure per-message costs:
- SMS rate per message (KSH)
- USSD rate per session (KSH)
- Sponsor subsidy percentage (0-100%)
- Partner rates & integrations

#### Premium Features
Enable/disable optional in-app purchases:
- Digital adoption certificates
- Personalized SMS alerts
- Advanced analytics
- Custom branding

#### Data Licensing
Approve research partnerships:
- Researchers submit requests for anonymized datasets
- Admin approves/denies with pricing (KSH 2K–10K)
- Track data access & compliance

#### API Tiers
Control access & monetization:
- **Free**: Limited API calls/month
- **Paid**: Increased rate limits
- **Enterprise**: Unlimited + dedicated support

### 3. **Audit Logging** 📋
Track every admin action for governance:
- User approvals/denials
- Configuration changes
- Monetization updates
- Data licensing decisions
- Compliance actions

**Fields logged:**
- Admin ID & name
- Action (e.g., `approve_user`, `update_package`)
- Entity type & ID
- Old & new values (JSON)
- IP address & timestamp

**Features:**
- Filterable by admin, action, entity type
- Search & sort capabilities
- CSV export for compliance reports
- Queryable history for audits

---

## Database Schema

The system requires 8 new tables (created in `db/migrations_alerts_monetization.sql`):

1. **alerts** - Alert log & dismissal tracking
2. **audit_logs** - Admin action history
3. **sponsorship_packages** - Tier definitions
4. **ngo_licenses** - SaaS tier pricing
5. **sms_settings** - Messaging costs & rates
6. **premium_features** - In-app purchase catalog
7. **research_requests** - Data licensing requests
8. **api_access** - User API tier & key management
9. **compliance_flags** - Data quality issues (duplicate logs, GPS mismatch, etc)

### Running the Migration

```bash
# SSH into your server or run via MySQL client
mysql -u root -p mitihub < db/migrations_alerts_monetization.sql
```

---

## Files & Structure

### Core Files

- **`admin/alerts_functions.php`** - Backend functions for alerts, monetization, audit logging
- **`admin/_alerts_widget.php`** - Reusable alerts dashboard widget
- **`admin/monetization.php`** - Admin page for managing all monetization settings
- **`admin/audit-logs.php`** - Filterable audit trail viewer with CSV export
- **`db/migrations_alerts_monetization.sql`** - Database schema migration

### Modified Files

- **`admin/dashboard.php`** - Integrated alerts widget & monetization links
- **`admin/_nav.php`** - Added Monetization & Audit Logs links

---

## API Reference

### Alert Functions

```php
// Get pending alerts (max 10)
$alerts = get_pending_alerts(10);

// Get alert summary counts by type
$counts = get_alert_counts();
// Returns: ['pending_approval' => 5, 'flagged_issue' => 2, 'revenue' => 3, ...]

// Create new alert
create_alert('compliance', 'Missing Photo', 'Tree log #123 has no photo', 'warning', 123, 'tree_log');

// Mark alert as read/dismissed
mark_alert_read($alert_id);
dismiss_alert($alert_id);

// Trigger alerts (helpers)
trigger_user_approval_alert($user_id, $email, 'school');
trigger_compliance_alert('gps_mismatch', $tree_log_id, 'GPS data off by >100m');
trigger_revenue_alert($sponsorship_id, 50000, 'TechCorp Inc');
```

### Audit Logging

```php
// Log admin action to audit trail
log_admin_action(
    $admin_id,
    'approve_user',              // action
    'user',                       // entity_type
    $user_id,                     // entity_id
    'Approved school registration',  // details
    ['status' => 'pending'],      // old_values
    ['status' => 'active']        // new_values
);

// Get audit logs with filters
$logs = get_audit_logs(100, $admin_id, $action, $entity_type);
```

### Monetization Functions

#### Sponsorship Packages
```php
// Get all active packages
$packages = get_sponsorship_packages(true);

// Save new or update package
save_sponsorship_package([
    'name' => 'Gold',
    'price_ksh' => 50000,
    'max_trees' => 500,
    'includes_sms_subsidy' => true
]);
```

#### NGO Licensing
```php
// Get all NGO tiers
$tiers = get_ngo_licenses(true);

// Save tier
save_ngo_license([
    'tier_name' => 'Enterprise',
    'price_ksh_annual' => 100000,
    'max_schools' => 1000
]);
```

#### SMS Settings
```php
// Get single setting
$cost = get_sms_setting('sms_per_message_cost_ksh', 2.5);

// Get all settings
$settings = get_all_sms_settings();

// Update setting
update_sms_setting('sms_per_message_cost_ksh', '3.0');
```

#### Premium Features
```php
// Get all features
$features = get_premium_features();

// Toggle feature on/off
toggle_premium_feature($feature_id, true);

// Update feature price
update_premium_feature_price($feature_id, 500);
```

#### Data Licensing
```php
// Get pending requests
$requests = get_pending_research_requests();

// Approve with price
approve_research_request($request_id, $admin_id, 5000);

// Deny request
deny_research_request($request_id);
```

#### API Access
```php
// Get user's API access record
$access = get_user_api_access($user_id);

// Create API access
create_user_api_access($user_id, 'free');

// Upgrade tier
upgrade_api_tier($user_id, 'paid', 100000); // 100K calls/month
```

---

## Admin Pages

### 🚨 Dashboard (`admin/dashboard.php`)
- Overview KPIs (users, revenue, trees, etc)
- Quick-view alerts widget with pending approvals, flags, revenue (7-day), compliance
- Links to Monetization & Audit Logs

### 💰 Monetization (`admin/monetization.php`)
- **Sponsorship Packages**: Edit Bronze/Silver/Gold with pricing, duration, features
- **NGO Licensing**: Define SaaS tiers & pricing
- **SMS/USSD Rates**: Configure per-message costs & subsidy %
- **Premium Features**: Enable/disable & price in-app purchases
- **API Tiers**: (Future) Manage free/paid/enterprise API access

### 📋 Audit Logs (`admin/audit-logs.php`)
- Searchable audit trail of all admin actions
- Filter by admin, action, entity type
- View old/new values for updates
- CSV export for compliance & governance

---

## Integration Points

### Triggering Alerts Automatically

In your workflow code, add alerts at key events:

```php
// When a new user registers
trigger_user_approval_alert($user_id, $user_email, $role);

// When tree log data quality issue is detected
trigger_compliance_alert('gps_mismatch', $log_id, 'GPS off by 150m');

// When new sponsorship is created
trigger_revenue_alert($sponsorship_id, $amount_ksh, $sponsor_name);
```

### Audit Logging Integration

Wrap admin actions in `log_admin_action()`:

```php
// In user approval code
user_set_status($user_id, 'active');
log_admin_action(
    current_user()['id'],
    'approve_user',
    'user',
    $user_id,
    "Approved {$user['email']} as {$user['role']}"
);
```

---

## Configuration (Defaults)

Default monetization settings (in database):

```
SMS per message:           KSH 2.50
USSD per session:          KSH 1.50
Sponsor SMS subsidy:       50%
Carbon credit per tree:    KSH 500 (future)
Min research data price:   KSH 2,000
Max research data price:   KSH 10,000

Sponsorship Packages:
  Bronze:   KSH 5,000  (50 trees, 6 months)
  Silver:   KSH 15,000 (200 trees, 12 months, branding + reporting)
  Gold:     KSH 50,000 (500 trees, 12 months, full + SMS subsidy)

NGO Tiers:
  Basic:      KSH 5,000/year (10 schools, 10K API calls)
  Pro:        KSH 25,000/year (100 schools, 100K API calls, SMS + export)
  Enterprise: KSH 100,000/year (1K schools, 1M API calls, all features)

Premium Features:
  Digital Certificates:    KSH 500/cert (optional)
  Personalized SMS:        KSH 200/user (optional)
  Advanced Analytics:      KSH 1,000/month (optional)
```

All defaults can be edited via the admin **Monetization** page.

---

## Future Enhancements

- [ ] Email notifications for alerts (via SendGrid/Twilio)
- [ ] SMS alerts for critical compliance issues
- [ ] Webhook integrations for external systems
- [ ] Bulk research data approval workflows
- [ ] API key rotation & expiration
- [ ] Carbon credit marketplace integration (Verra, Gold Standard)
- [ ] Revenue dashboards & reporting
- [ ] Sponsor branding & announcement management
- [ ] Compliance certification workflows

---

## Testing & Local Development

### 1. Run the migration
```bash
mysql -u root -p mitihub < db/migrations_alerts_monetization.sql
```

### 2. Visit the admin pages locally
```
http://localhost/mitihub/admin/dashboard.php
http://localhost/mitihub/admin/monetization.php
http://localhost/mitihub/admin/audit-logs.php
```

### 3. Trigger test alerts (admin/dashboard.php)
```php
// Add to dashboard for testing
create_alert('pending_approval', 'Test Alert', 'This is a test', 'critical');
```

### 4. Export audit logs
Visit Audit Logs page → click **📥 Export CSV** to verify export functionality.

---

## Security Notes

1. **CSRF Protection**: All forms use `csrf_token()` or `csrf_token_global()`
2. **Role-Based Access**: All admin pages check `require_role('admin')`
3. **Audit Trail**: All actions logged with IP & timestamp for forensics
4. **Data Sensitivity**: Research requests & API keys stored securely
5. **Encryption**: Consider encrypting API keys at rest (future)

---

## Support & Troubleshooting

### Issue: Alerts widget not showing
- Ensure `admin/alerts_functions.php` is required in dashboard
- Check database tables were created via migration

### Issue: Monetization settings not saving
- Verify CSRF token is present in form
- Check database permissions for UPDATE queries
- Review error logs for database connection issues

### Issue: Audit logs empty
- Ensure `log_admin_action()` is being called in your workflows
- Check database insert permissions

---

## Summary

The Admin Alerts & Monetization system transforms MitiHub into a full **SaaS platform** with:
- ✅ Real-time governance alerts
- ✅ Multiple revenue streams (sponsorships, SaaS licensing, data sales, API tiers)
- ✅ Compliance & audit tracking
- ✅ Flexible, configurable pricing
- ✅ Role-based access control

This makes MitiHub the **"Shopify for Community Reforestation"** by balancing **impact + revenue** while maintaining **transparency & accountability**.
