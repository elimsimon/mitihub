# Quick Setup Checklist: Admin Alerts & Monetization

## ✅ Implementation Complete

The MitiHub Admin **Alerts & Governance** and **Monetization Control** systems are now fully implemented. Here's what was added:

### New Files Created

```
✅ admin/alerts_functions.php               - Core backend functions
✅ admin/_alerts_widget.php                  - Reusable dashboard widget
✅ admin/monetization.php                    - Monetization settings page
✅ admin/audit-logs.php                      - Audit trail viewer
✅ db/migrations_alerts_monetization.sql    - Database schema
✅ ALERTS_MONETIZATION_GUIDE.md              - Developer documentation
```

### Files Modified

```
✅ admin/dashboard.php                       - Integrated alerts widget
✅ admin/_nav.php                            - Added nav links
```

---

## 🚀 Setup Steps (Local Dev)

### Step 1: Run Database Migration

Connect to your MySQL and run:

```bash
mysql -u root -p mitihub < db/migrations_alerts_monetization.sql
```

Or use phpMyAdmin:
1. Navigate to `http://localhost/phpmyadmin`
2. Select database `mitihub`
3. Click **Import**
4. Upload `db/migrations_alerts_monetization.sql`
5. Click **Go**

### Step 2: Restart Apache (XAMPP)

```
Windows: XAMPP Control Panel → Apache → Restart
Mac/Linux: sudo systemctl restart apache2 (or restart your web server)
```

### Step 3: Test the New Features

Visit these URLs in your browser:

```
Admin Dashboard:
http://localhost/mitihub/admin/dashboard.php

Monetization Settings:
http://localhost/mitihub/admin/monetization.php

Audit Logs Viewer:
http://localhost/mitihub/admin/audit-logs.php
```

---

## 📊 Features Implemented

### 🚨 Alerts System
- ✅ Pending Approvals (schools, sponsors)
- ✅ Flagged Issues (data quality)
- ✅ Revenue Alerts (sponsorships)
- ✅ Compliance Alerts (MRV data)
- ✅ Mark as read/dismissed

### 💰 Monetization Controls
- ✅ Sponsorship Packages (Bronze/Silver/Gold)
- ✅ NGO Licensing Tiers (Basic/Pro/Enterprise)
- ✅ SMS/USSD Rate Configuration
- ✅ Premium Features Management
- ✅ Data Licensing Approvals
- ✅ API Tier Access Control

### 📋 Audit Logging
- ✅ Complete action history
- ✅ Old/new value tracking
- ✅ Filterable by admin/action/entity
- ✅ CSV export for compliance

---

## 💡 Quick Usage Examples

### Trigger an Alert (In Your Workflow Code)

```php
// When a new school registers
trigger_user_approval_alert($user_id, $email, 'school');

// When tree log has GPS error
trigger_compliance_alert('gps_mismatch', $log_id, 'GPS off by >100m');

// When sponsorship is created
trigger_revenue_alert($sponsorship_id, 50000, 'TechCorp Inc');
```

### Log an Admin Action (In Approval Code)

```php
// Approve a user
user_set_status($user_id, 'active');

// Log the action
log_admin_action(
    current_user()['id'],     // admin_id
    'approve_user',           // action
    'user',                   // entity_type
    $user_id,                 // entity_id
    "Approved {$email}"       // details
);
```

### Get Alert Counts (In Dashboard)

```php
$counts = get_alert_counts();
echo "Pending: " . $counts['total'];
echo "Critical: " . $counts['critical'];
echo "Compliance: " . $counts['compliance'];
```

---

## 🎯 Next Steps (Optional)

### Phase 2 (Future)
- [ ] Email notifications for critical alerts
- [ ] SMS alerts via Twilio/Safaricom
- [ ] Webhook integrations
- [ ] Advanced compliance scoring
- [ ] Carbon credit marketplace
- [ ] Revenue dashboards & KPI tracking
- [ ] Sponsor branding manager
- [ ] Bulk research data workflows

### Phase 3 (Advanced)
- [ ] API key encryption at rest
- [ ] Two-factor authentication for admin
- [ ] IP whitelist for admin access
- [ ] Compliance certification workflows
- [ ] External audit integration

---

## 📝 Configuration (Defaults)

All default settings can be edited in the **Monetization** page:

**SMS Costs:**
- Per SMS: KSH 2.50
- Per USSD: KSH 1.50
- Sponsor subsidy: 50%

**Sponsorship Packages:**
- Bronze: KSH 5,000 (50 trees)
- Silver: KSH 15,000 (200 trees + branding)
- Gold: KSH 50,000 (500 trees + SMS subsidy)

**NGO Tiers:**
- Basic: KSH 5,000/year (10 schools)
- Pro: KSH 25,000/year (100 schools + SMS)
- Enterprise: KSH 100,000/year (unlimited)

---

## ❓ Troubleshooting

### Issue: "Table 'alerts' doesn't exist"
**Solution:** Run the database migration (Step 1 above)

### Issue: Alerts widget not appearing on dashboard
**Solution:** Verify `admin/alerts_functions.php` is properly required
```php
require_once ROOT_PATH . '/admin/alerts_functions.php';
```

### Issue: Monetization page shows blank
**Solution:** Clear browser cache (Ctrl+F5) and verify database tables exist

### Issue: Audit logs not recording
**Solution:** Ensure `log_admin_action()` is called in your workflows with proper parameters

---

## 📚 Documentation

Full developer documentation is in:
**`ALERTS_MONETIZATION_GUIDE.md`**

This includes:
- API reference for all functions
- Database schema details
- Integration examples
- Security best practices
- Testing instructions

---

## 🎉 Summary

Your admin system now includes:
- ✅ Real-time governance alerts
- ✅ Multiple revenue streams (sponsorships, SaaS, data licensing, API tiers)
- ✅ Complete audit trail for compliance
- ✅ Flexible monetization configuration
- ✅ Professional admin UI with responsive design

MitiHub is now positioned as the **"Shopify for Community Reforestation"** with:
- **Impact Focus**: Data integrity, compliance, governance
- **Revenue Focus**: Multiple monetization levers controlled by admin
- **Transparency**: Complete audit trail of all actions

---

## 🔗 Admin Navigation

From the admin nav, you can now access:
- 📊 **Dashboard** - Overview & alerts widget
- 👥 **Users** - User management & approvals
- 💰 **Monetization** - Configure packages, tiers, rates (NEW)
- 📋 **Audit Logs** - Track all admin actions (NEW)
- 🌳 **Tree Logs** - Tree planting records
- 🏆 **Leaderboards** - Top performers
- 📢 **Announcements** - System messages
- 📊 **Reports** - Analytics & exports
- 🤝 **Sponsors** - Sponsor management
- ⚙️ **Settings** - System configuration
- 👤 **Profile** - Admin profile

---

## 🎓 Training Tip

To test the alerts system:
1. Use `admin/user-management.php` to approve pending users
2. Watch alerts update in real-time on the dashboard
3. Check `admin/audit-logs.php` to see your actions logged
4. Edit monetization settings and verify audit trail

---

**Implementation Date:** November 11, 2025
**Status:** ✅ PRODUCTION READY

Questions? See `ALERTS_MONETIZATION_GUIDE.md` for full API documentation.
