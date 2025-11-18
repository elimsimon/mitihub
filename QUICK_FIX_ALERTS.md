# ⚡ Quick Fix: Alerts Migration Setup

The database tables for the alerts and monetization system need to be created. Here's how to fix it:

## Option 1: One-Click Migration (Easiest) ✅

1. **Open your browser** and go to:
   ```
   http://localhost/mitihub/setup_alerts_migration.php
   ```

2. **Click the "▶️ Run Migration" button**

3. **Done!** The page will show:
   ```
   ✅ Success!
   Migration completed successfully! Executed X statements.
   ```

4. **Go back to Admin Dashboard:**
   ```
   http://localhost/mitihub/admin/dashboard.php
   ```

---

## Option 2: Manual SQL (If Option 1 Fails)

### Via phpMyAdmin:
1. Open `http://localhost/phpmyadmin`
2. Select database `mitihub`
3. Click **Import** tab
4. Click **Choose File** and select `db/migrations_alerts_monetization.sql` (from your project root)
5. Click **Go**

### Via Command Line (Windows):
```powershell
mysql -u root -p mitihub < db/migrations_alerts_monetization.sql
```
(Enter your MySQL password when prompted)

### Via Command Line (Mac/Linux):
```bash
mysql -u root -p mitihub < db/migrations_alerts_monetization.sql
```

---

## What Gets Created

The migration creates these 9 tables:

✅ `alerts` - Alert log & dismissal tracking  
✅ `audit_logs` - Admin action history  
✅ `sponsorship_packages` - Tier definitions  
✅ `ngo_licenses` - SaaS pricing  
✅ `sms_settings` - Messaging costs  
✅ `premium_features` - In-app purchases  
✅ `research_requests` - Data licensing  
✅ `api_access` - API tier management  
✅ `compliance_flags` - Data quality issues  

---

## Verify It Worked

After running the migration, you should see:

1. ✅ **Admin Dashboard** loads without errors
2. ✅ **Alerts Widget** shows on dashboard (with 0 alerts initially)
3. ✅ **💰 Monetization** button works
4. ✅ **📋 Audit Logs** button works

---

## Troubleshooting

### Issue: "Access denied for user 'root'@'localhost'"
**Solution:** Your MySQL password is different. Try:
```powershell
mysql -u root mitihub < db/migrations_alerts_monetization.sql
```
(without the `-p` flag, if no password is set)

### Issue: "Migration failed" in the web UI
**Solution:** Try the manual SQL command line approach instead. Check MySQL error logs for details.

### Issue: "File not found"
**Solution:** Make sure you're in the project root directory:
```powershell
cd C:\xampp\htdocs\mitihub
mysql -u root -p mitihub < db/migrations_alerts_monetization.sql
```

---

## After Migration

- ✅ Dashboard alerts widget works
- ✅ Monetization page is editable
- ✅ Audit logs are recorded
- ✅ Start using the admin features!

---

## Need Help?

See the full documentation:
- **`ALERTS_MONETIZATION_GUIDE.md`** - Complete API reference
- **`SETUP_ALERTS_CHECKLIST.md`** - Full setup instructions

---

**Status:** Quick setup pages created ✅  
**Next:** Run the migration to enable all features
