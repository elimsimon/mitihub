<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$weights = [
    'survival' => 50,
    'adoption' => 30,
    'engagement' => 20,
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Leaderboards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">Leaderboards & Badges</h1>

  <div class="card p-3 mb-3">
    <h2 class="h5">Ranking Weights</h2>
    <form method="post">
      <div class="row g-2">
        <div class="col-md-4"><label class="form-label">Survival (%)</label><input type="number" name="w_survival" value="<?php echo $weights['survival']; ?>" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Adoption (%)</label><input type="number" name="w_adoption" value="<?php echo $weights['adoption']; ?>" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Engagement (%)</label><input type="number" name="w_engagement" value="<?php echo $weights['engagement']; ?>" class="form-control"></div>
      </div>
      <button class="btn btn-primary mt-3">Save</button>
    </form>
  </div>

  <div class="card p-3 mb-3">
    <h2 class="h5">Badges</h2>
    <p>Approve new badges (Eco Hero, Sponsor-branded).</p>
    <button class="btn btn-secondary">Review Badge Requests</button>
  </div>

  <div class="card p-3">
    <h2 class="h5">National Leaderboard</h2>
    <form class="row g-2 mb-2">
      <div class="col-md-4"><input name="county" class="form-control" placeholder="Filter by county"></div>
      <div class="col-md-4"><input name="school" class="form-control" placeholder="Filter by school"></div>
      <div class="col-md-4"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </form>
    <div class="alert alert-info">Leaderboard data source pending (species survival, adoption, engagement). Override rankings if disputes arise.</div>
  </div>
</div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
