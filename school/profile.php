<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');
$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

// Try to load current school profile if table/record exists; fail-safe to empty values
$profile = [
  'name' => $user['school_name'] ?? '',
  'address' => '',
  'website' => '',
  'club_name' => '',
  'patrons' => '',
  'member_count' => '',
  'logo_path' => '',
];
try {
    if ($school_id) {
        $stmt = db()->prepare('SELECT name, address, website, club_name, patrons, member_count, logo_path FROM schools WHERE id=? LIMIT 1');
        $stmt->execute([$school_id]);
        $row = $stmt->fetch();
        if ($row) { $profile = array_merge($profile, $row); }
    }
} catch (Throwable $e) {
    // Table may not exist yet; ignore and keep defaults
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>School Profile Settings</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/school.css'); ?>">
</head>
<body>
  <button class="toggle" id="sidebarToggle" aria-label="Toggle menu">☰ Menu</button>
  <div class="layout">
    <aside class="sidebar" id="sidebar">
      <div class="brand">School Panel</div>
      <div class="muted small" style="padding: 0 8px 8px;">
        <?php echo htmlspecialchars($user['name'] ?? ''); ?> — Role: School Admin
      </div>
      <ul class="nav">
        <li><a href="<?php echo base_url('school/dashboard.php'); ?>">Home</a></li>
        <li><a href="<?php echo base_url('school/profile.php'); ?>" class="active">Profile Settings</a></li>
        <li><a href="<?php echo base_url('logout.php'); ?>">Logout</a></li>
      </ul>
    </aside>

    <main class="content">
      <section class="card">
        <h1 class="section-title">Profile Settings</h1>
        <p class="muted">Manage your school profile and club details. Saving is a placeholder until backend endpoints are implemented.</p>

        <form method="post" action="#" enctype="multipart/form-data" onsubmit="return false;">
          <div class="grid">
            <div class="col-6">
              <label>School Name
                <input type="text" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" placeholder="Your School Name">
              </label>
            </div>
            <div class="col-6">
              <label>Website
                <input type="text" name="website" value="<?php echo htmlspecialchars($profile['website']); ?>" placeholder="https://example.edu">
              </label>
            </div>
            <div class="col-12">
              <label>Address
                <input type="text" name="address" value="<?php echo htmlspecialchars($profile['address']); ?>" placeholder="Address">
              </label>
            </div>
            <div class="col-6">
              <label>Club Name
                <input type="text" name="club_name" value="<?php echo htmlspecialchars($profile['club_name']); ?>" placeholder="Environmental Club">
              </label>
            </div>
            <div class="col-3">
              <label>Patrons
                <input type="text" name="patrons" value="<?php echo htmlspecialchars($profile['patrons']); ?>" placeholder="e.g., Jane Doe">
              </label>
            </div>
            <div class="col-3">
              <label>Member Count
                <input type="text" name="member_count" value="<?php echo htmlspecialchars((string)$profile['member_count']); ?>" placeholder="e.g., 42">
              </label>
            </div>
            <div class="col-12">
              <label>Logo / Profile Image
                <input type="file" name="logo">
              </label>
              <?php if (!empty($profile['logo_path'])): ?>
                <div class="small muted" style="margin-top:6px;">Current: <?php echo htmlspecialchars($profile['logo_path']); ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="actions">
            <button class="btn alt" type="submit" disabled>Save Changes (Coming Soon)</button>
          </div>
        </form>
      </section>

      <section class="card" style="margin-top:12px;">
        <h2 class="section-title">Security</h2>
        <ul class="muted" style="font-size:14px;">
          <li>Change password: <a href="<?php echo base_url('public/change_password.php'); ?>">Change Password</a></li>
          <li>Logout: <a href="<?php echo base_url('logout.php'); ?>">Sign out</a></li>
        </ul>
      </section>
    </main>
  </div>

  <script>
    (function(){
      const sidebar = document.getElementById('sidebar');
      const toggle = document.getElementById('sidebarToggle');
      if (toggle) {
        toggle.addEventListener('click', function(){ sidebar.classList.toggle('open'); });
      }
    })();
  </script>
</body>
</html>
