<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();

function export_csv($filename, array $headers, array $rows): void {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output','w');
    fputcsv($out, $headers);
    foreach ($rows as $r) { fputcsv($out, $r); }
    fclose($out);
    exit;
}

$type = $_GET['type'] ?? '';
if ($type === 'global') {
    $rows = $pdo->query("SELECT county, AVG(survival_pct) AS survival_pct FROM counties GROUP BY county")->fetchAll();
    export_csv('global_impact.csv', ['County','Survival %'], array_map(fn($r)=>[$r['county'],$r['survival_pct']], $rows));
}
if ($type === 'sponsors') {
    $rows = $pdo->query("SELECT s.name AS sponsor, COALESCE(SUM(p.amount),0) AS total FROM sponsors s LEFT JOIN payments p ON p.sponsor_id=s.id GROUP BY s.id, s.name")->fetchAll();
    export_csv('sponsor_impact.csv', ['Sponsor','Total Amount'], array_map(fn($r)=>[$r['sponsor'],$r['total']], $rows));
}
if ($type === 'schools') {
    $rows = $pdo->query("SELECT sc.name AS school, COUNT(sa.id) AS adoptions FROM schools sc LEFT JOIN sponsorships sa ON sa.school_id=sc.id GROUP BY sc.id, sc.name")->fetchAll();
    export_csv('school_adoptions.csv', ['School','Adoptions'], array_map(fn($r)=>[$r['school'],$r['adoptions']], $rows));
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports & Analytics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">Reports & Analytics</h1>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <h2 class="h6">Global Impact</h2>
        <p>Nationwide survival %.</p>
        <a class="btn btn-sm btn-primary" href="?type=global">Export CSV</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h2 class="h6">Sponsor Impact</h2>
        <p>Sponsor totals.</p>
        <a class="btn btn-sm btn-primary" href="?type=sponsors">Export CSV</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h2 class="h6">School Adoptions</h2>
        <p>Adoptions by schools.</p>
        <a class="btn btn-sm btn-primary" href="?type=schools">Export CSV</a>
      </div>
    </div>
  </div>
      <div class="alert alert-info mt-3">Advanced analytics (species survival by region, engagement trends, adoption-to-survival correlation, carbon-ready datasets) to be added.</div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
