<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/admin/alerts_functions.php';
require_role('admin');

$admin = current_user();
$tab = $_GET['tab'] ?? 'packages';
$msg = null;

// Handle POST requests for different monetization settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf'] ?? '';
    if (!csrf_check($csrf_token) && !csrf_check_global($csrf_token)) {
        $msg = ['error' => 'Invalid CSRF token'];
    } else {
        switch ($tab) {
            case 'packages':
                if (isset($_POST['action']) && $_POST['action'] === 'save_package') {
                    $pkg_data = [
                        'id' => $_POST['pkg_id'] ?? null,
                        'name' => $_POST['name'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'price_ksh' => (int)($_POST['price_ksh'] ?? 0),
                        'max_trees' => (int)($_POST['max_trees'] ?? 0),
                        'max_duration_months' => (int)($_POST['max_duration_months'] ?? 0),
                        'includes_branding' => isset($_POST['includes_branding']),
                        'includes_reporting' => isset($_POST['includes_reporting']),
                        'includes_sms_subsidy' => isset($_POST['includes_sms_subsidy']),
                        'is_active' => isset($_POST['is_active'])
                    ];
                    if (save_sponsorship_package($pkg_data)) {
                        $msg = ['success' => 'Package saved successfully'];
                        log_admin_action($admin['id'], 'update_package', 'sponsorship_package', $pkg_data['id'] ?? 0, 'Updated sponsorship package: ' . $pkg_data['name']);
                    } else {
                        $msg = ['error' => 'Failed to save package'];
                    }
                }
                break;

            case 'ngo':
                if (isset($_POST['action']) && $_POST['action'] === 'save_license') {
                    $lic_data = [
                        'id' => $_POST['lic_id'] ?? null,
                        'tier_name' => $_POST['tier_name'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'price_ksh_annual' => (int)($_POST['price_ksh_annual'] ?? 0),
                        'max_schools' => (int)($_POST['max_schools'] ?? 0),
                        'max_api_calls_monthly' => (int)($_POST['max_api_calls_monthly'] ?? 0),
                        'includes_analytics' => isset($_POST['includes_analytics']),
                        'includes_sms_gateway' => isset($_POST['includes_sms_gateway']),
                        'includes_data_export' => isset($_POST['includes_data_export']),
                        'includes_custom_branding' => isset($_POST['includes_custom_branding']),
                        'is_active' => isset($_POST['is_active'])
                    ];
                    if (save_ngo_license($lic_data)) {
                        $msg = ['success' => 'License tier saved successfully'];
                        log_admin_action($admin['id'], 'update_license', 'ngo_license', $lic_data['id'] ?? 0, 'Updated NGO license: ' . $lic_data['tier_name']);
                    } else {
                        $msg = ['error' => 'Failed to save license'];
                    }
                }
                break;

            case 'sms':
                if (isset($_POST['setting_key']) && isset($_POST['setting_value'])) {
                    if (update_sms_setting($_POST['setting_key'], $_POST['setting_value'])) {
                        $msg = ['success' => 'SMS setting updated'];
                        log_admin_action($admin['id'], 'update_sms_setting', 'sms_settings', 0, 'Updated SMS setting: ' . $_POST['setting_key']);
                    } else {
                        $msg = ['error' => 'Failed to update setting'];
                    }
                }
                break;

            case 'features':
                if (isset($_POST['action'])) {
                    if ($_POST['action'] === 'toggle_feature') {
                        $feature_id = (int)($_POST['feature_id'] ?? 0);
                        $is_enabled = isset($_POST['is_enabled']);
                        if (toggle_premium_feature($feature_id, $is_enabled)) {
                            $msg = ['success' => 'Feature status updated'];
                            log_admin_action($admin['id'], 'toggle_feature', 'premium_feature', $feature_id, 'Toggled premium feature');
                        }
                    } elseif ($_POST['action'] === 'update_price') {
                        $feature_id = (int)($_POST['feature_id'] ?? 0);
                        $price = (int)($_POST['price_ksh'] ?? 0);
                        if (update_premium_feature_price($feature_id, $price)) {
                            $msg = ['success' => 'Feature price updated'];
                            log_admin_action($admin['id'], 'update_feature_price', 'premium_feature', $feature_id, "Updated price to KSH {$price}");
                        }
                    }
                }
                break;
        }
    }
}

// Get data for current tab
$packages = get_sponsorship_packages(false);
$ngo_licenses = get_ngo_licenses(false);
$sms_settings = get_all_sms_settings();
$premium_features = get_premium_features();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Monetization Settings - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
  <style>
    .nav-tabs .nav-link { color: #666; border: none; border-bottom: 2px solid transparent; }
    .nav-tabs .nav-link.active { color: #2f6fed; border-bottom-color: #2f6fed; background: none; }
    .monetization-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
    .form-section { margin-bottom: 2rem; }
    .tab-content { background: #fff; padding: 1.5rem; border-radius: 8px; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>

<div class="container-fluid py-3">
  <h1 class="h2 mb-4">💰 Monetization & Governance Settings</h1>

  <?php if ($msg): ?>
    <div class="alert alert-<?php echo isset($msg['error']) ? 'danger' : 'success'; ?>" role="alert">
      <?php echo htmlspecialchars($msg['error'] ?? $msg['success']); ?>
    </div>
  <?php endif; ?>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <a class="nav-link <?php echo $tab === 'packages' ? 'active' : ''; ?>" href="?tab=packages">Sponsorship Packages</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $tab === 'ngo' ? 'active' : ''; ?>" href="?tab=ngo">NGO Licensing</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $tab === 'sms' ? 'active' : ''; ?>" href="?tab=sms">SMS/USSD Rates</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $tab === 'features' ? 'active' : ''; ?>" href="?tab=features">Premium Features</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo $tab === 'api' ? 'active' : ''; ?>" href="?tab=api">API Tiers</a>
    </li>
  </ul>

  <div class="tab-content">

    <!-- SPONSORSHIP PACKAGES TAB -->
    <?php if ($tab === 'packages'): ?>
      <div class="form-section">
        <h3>Sponsorship Packages (Bronze/Silver/Gold)</h3>
        <p class="text-muted">Define sponsorship tiers that corporate sponsors can purchase.</p>

        <div class="row">
          <?php foreach ($packages as $pkg): ?>
            <div class="col-md-4 mb-3">
              <div class="monetization-card">
                <h5><?php echo htmlspecialchars($pkg['name']); ?> 
                  <?php echo $pkg['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?>
                </h5>
                <p class="text-muted small"><?php echo htmlspecialchars($pkg['description']); ?></p>
                <div class="my-3">
                  <strong>Price:</strong> KSH <?php echo number_format($pkg['price_ksh']); ?><br>
                  <strong>Max Trees:</strong> <?php echo $pkg['max_trees']; ?><br>
                  <strong>Duration:</strong> <?php echo $pkg['max_duration_months']; ?> months
                </div>
                <div class="small mb-3">
                  <?php echo $pkg['includes_branding'] ? '✓ Branding' : ''; ?>
                  <?php echo $pkg['includes_reporting'] ? ' • ✓ Reporting' : ''; ?>
                  <?php echo $pkg['includes_sms_subsidy'] ? ' • ✓ SMS Subsidy' : ''; ?>
                </div>
                <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPackageModal" 
                   onclick="loadPackageForm(<?php echo htmlspecialchars(json_encode($pkg)); ?>)">
                  Edit
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#editPackageModal" onclick="resetPackageForm()">
          + Add Package
        </button>
      </div>

      <!-- Edit Package Modal -->
      <div class="modal fade" id="editPackageModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Sponsorship Package</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="action" value="save_package">
                <input type="hidden" name="pkg_id" id="pkg_id">

                <div class="mb-3">
                  <label class="form-label">Package Name</label>
                  <input type="text" name="name" id="pkg_name" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea name="description" id="pkg_desc" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Price (KSH)</label>
                  <input type="number" name="price_ksh" id="pkg_price" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Max Trees</label>
                  <input type="number" name="max_trees" id="pkg_trees" class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Duration (months)</label>
                  <input type="number" name="max_duration_months" id="pkg_duration" class="form-control">
                </div>
                <div class="form-check">
                  <input type="checkbox" name="includes_branding" id="pkg_branding" class="form-check-input">
                  <label class="form-check-label" for="pkg_branding">Includes Branding</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" name="includes_reporting" id="pkg_reporting" class="form-check-input">
                  <label class="form-check-label" for="pkg_reporting">Includes Reporting</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" name="includes_sms_subsidy" id="pkg_sms" class="form-check-input">
                  <label class="form-check-label" for="pkg_sms">Includes SMS Subsidy</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" name="is_active" id="pkg_active" class="form-check-input" checked>
                  <label class="form-check-label" for="pkg_active">Active</label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Package</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <!-- NGO LICENSING TAB -->
    <?php elseif ($tab === 'ngo'): ?>
      <div class="form-section">
        <h3>NGO & Government Licensing Tiers</h3>
        <p class="text-muted">Define SaaS tiers (Basic/Pro/Enterprise) sold to NGOs and county governments.</p>

        <div class="row">
          <?php foreach ($ngo_licenses as $lic): ?>
            <div class="col-md-4 mb-3">
              <div class="monetization-card">
                <h5><?php echo htmlspecialchars($lic['tier_name']); ?>
                  <?php echo $lic['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?>
                </h5>
                <p class="text-muted small"><?php echo htmlspecialchars($lic['description']); ?></p>
                <div class="my-3">
                  <strong>Annual Price:</strong> KSH <?php echo number_format($lic['price_ksh_annual']); ?><br>
                  <strong>Max Schools:</strong> <?php echo $lic['max_schools']; ?><br>
                  <strong>API Calls/month:</strong> <?php echo number_format($lic['max_api_calls_monthly']); ?>
                </div>
                <div class="small mb-3">
                  <?php echo $lic['includes_analytics'] ? '✓ Analytics' : ''; ?>
                  <?php echo $lic['includes_sms_gateway'] ? ' • ✓ SMS' : ''; ?>
                  <?php echo $lic['includes_data_export'] ? ' • ✓ Export' : ''; ?>
                  <?php echo $lic['includes_custom_branding'] ? ' • ✓ Branding' : ''; ?>
                </div>
                <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    <!-- SMS/USSD RATES TAB -->
    <?php elseif ($tab === 'sms'): ?>
      <div class="form-section">
        <h3>SMS/USSD Monetization Settings</h3>
        <p class="text-muted">Configure messaging costs and partner subsidy percentages.</p>

        <div class="row">
          <?php foreach ($sms_settings as $setting): ?>
            <div class="col-md-6 mb-3">
              <div class="monetization-card">
                <form method="POST" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                  <input type="hidden" name="tab" value="sms">
                  <input type="hidden" name="setting_key" value="<?php echo htmlspecialchars($setting['setting_key']); ?>">
                  <div style="flex: 1;">
                    <label class="form-label small"><strong><?php echo htmlspecialchars($setting['setting_key']); ?></strong></label>
                    <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($setting['description']); ?></small>
                    <input type="<?php echo $setting['data_type'] === 'number' ? 'number' : 'text'; ?>" 
                           name="setting_value" 
                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>" 
                           class="form-control form-control-sm"
                           <?php echo !$setting['is_editable'] ? 'disabled' : ''; ?>>
                  </div>
                  <?php if ($setting['is_editable']): ?>
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    <!-- PREMIUM FEATURES TAB -->
    <?php elseif ($tab === 'features'): ?>
      <div class="form-section">
        <h3>Premium Features</h3>
        <p class="text-muted">Enable/disable premium in-app purchases (digital certificates, SMS alerts, etc).</p>

        <table class="table table-hover">
          <thead>
            <tr style="background: #f3f6fb;">
              <th>Feature</th>
              <th>Price (KSH)</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($premium_features as $feat): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($feat['feature_name']); ?></strong><br>
                  <small class="text-muted"><?php echo htmlspecialchars($feat['description']); ?></small>
                </td>
                <td>KSH <?php echo $feat['price_ksh']; ?></td>
                <td>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                    <input type="hidden" name="tab" value="features">
                    <input type="hidden" name="action" value="toggle_feature">
                    <input type="hidden" name="feature_id" value="<?php echo $feat['id']; ?>">
                    <?php if ($feat['is_enabled']): ?>
                      <span class="badge bg-success">Enabled</span>
                      <input type="hidden" name="is_enabled" value="0">
                      <button type="submit" class="btn btn-xs btn-danger" style="font-size: 0.75rem;">Disable</button>
                    <?php else: ?>
                      <span class="badge bg-secondary">Disabled</span>
                      <input type="hidden" name="is_enabled" value="1">
                      <button type="submit" class="btn btn-xs btn-success" style="font-size: 0.75rem;">Enable</button>
                    <?php endif; ?>
                  </form>
                </td>
                <td><a href="#" class="btn btn-sm btn-outline-secondary">Edit Price</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- API TIERS TAB -->
    <?php elseif ($tab === 'api'): ?>
      <div class="form-section">
        <h3>API Access Management</h3>
        <p class="text-muted">Control API tier access (Free/Paid/Enterprise) and rate limits.</p>
        <div class="alert alert-info">
          <strong>Future Implementation:</strong> Manage per-user API keys, tier upgrades, and monthly rate limit resets.
        </div>
      </div>

    <?php endif; ?>

  </div>
    </main>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadPackageForm(pkg) {
  document.getElementById('pkg_id').value = pkg.id || '';
  document.getElementById('pkg_name').value = pkg.name || '';
  document.getElementById('pkg_desc').value = pkg.description || '';
  document.getElementById('pkg_price').value = pkg.price_ksh || '';
  document.getElementById('pkg_trees').value = pkg.max_trees || '';
  document.getElementById('pkg_duration').value = pkg.max_duration_months || '';
  document.getElementById('pkg_branding').checked = pkg.includes_branding ? true : false;
  document.getElementById('pkg_reporting').checked = pkg.includes_reporting ? true : false;
  document.getElementById('pkg_sms').checked = pkg.includes_sms_subsidy ? true : false;
  document.getElementById('pkg_active').checked = pkg.is_active ? true : false;
}

function resetPackageForm() {
  document.getElementById('pkg_id').value = '';
  document.getElementById('pkg_name').value = '';
  document.getElementById('pkg_desc').value = '';
  document.getElementById('pkg_price').value = '';
  document.getElementById('pkg_trees').value = '';
  document.getElementById('pkg_duration').value = '';
  document.getElementById('pkg_branding').checked = false;
  document.getElementById('pkg_reporting').checked = false;
  document.getElementById('pkg_sms').checked = false;
  document.getElementById('pkg_active').checked = true;
}
</script>
</body>
</html>
