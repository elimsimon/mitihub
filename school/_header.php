<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');
$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>School Panel - <?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
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
        <li><a href="<?php echo base_url('school/dashboard.php'); ?>" <?php echo ($current_page === 'dashboard') ? 'class="active"' : ''; ?>>Home</a></li>
        <li><a href="<?php echo base_url('school/mytrees.php'); ?>" <?php echo ($current_page === 'mytrees') ? 'class="active"' : ''; ?>>My Trees</a></li>
        <li><a href="<?php echo base_url('school/map.php'); ?>" <?php echo ($current_page === 'map') ? 'class="active"' : ''; ?>>Tree Map</a></li>
        <li><a href="<?php echo base_url('school/points.php'); ?>" <?php echo ($current_page === 'points') ? 'class="active"' : ''; ?>>Points & Badges</a></li>
        <li><a href="<?php echo base_url('school/qr.php'); ?>" <?php echo ($current_page === 'qr') ? 'class="active"' : ''; ?>>QR Scanner</a></li>
        <li><a href="<?php echo base_url('school/leaderboard.php'); ?>" <?php echo ($current_page === 'leaderboard') ? 'class="active"' : ''; ?>>Leaderboard</a></li>
        <li><a href="<?php echo base_url('school/reports.php'); ?>" <?php echo ($current_page === 'reports') ? 'class="active"' : ''; ?>>Reports</a></li>
        <li><a href="<?php echo base_url('school/settings.php'); ?>" <?php echo ($current_page === 'settings') ? 'class="active"' : ''; ?>>Settings</a></li>
        <li><a href="<?php echo base_url('school/sponsorships.php'); ?>" <?php echo ($current_page === 'sponsorships') ? 'class="active"' : ''; ?>>Sponsorships</a></li>
      </ul>
    </aside>

    <main class="content">