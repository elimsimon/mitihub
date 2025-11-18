<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

// Placeholder data
$stats = [
  'trees_planted' => 0,
  'trees_adopted' => 0,
];
$my_planted_trees = [];
$adoptable_trees = [];

$page_title = 'My Trees';
$current_page = 'mytrees';
include '_header.php';
?>

<!-- My Trees -->
<section class="card">
  <h2 class="section-title">My Trees</h2>

  <div class="tree-list">
    <h3>Planted Trees</h3>
    <?php if (empty($my_planted_trees)): ?>
      <p class="muted">No trees planted yet.</p>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Tree ID</th>
              <th>Species</th>
              <th>Date Planted</th>
              <th>Health Status</th>
              <th>Survival Rate</th>
              <th>Age</th>
              <th>GPS Coordinates</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($my_planted_trees as $t): ?>
              <tr>
                <td><?php echo htmlspecialchars($t['id']); ?></td>
                <td><?php echo htmlspecialchars($t['species']); ?></td>
                <td><?php echo htmlspecialchars($t['date_planted']); ?></td>
                <td class="status-<?php echo htmlspecialchars($t['health_status']); ?>"><?php echo htmlspecialchars($t['health_status']); ?></td>
                <td><?php echo htmlspecialchars($t['survival_rate']); ?>%</td>
                <td><?php echo htmlspecialchars($t['age']); ?></td>
                <td><?php echo htmlspecialchars($t['lat'] . ', ' . $t['lng']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="tree-list">
    <h3>Plant a Tree</h3>
    <form method="post" action="#" onsubmit="return false;">
      <div class="form-grid">
        <div>
          <label>Tree Species
            <select name="species_id" id="speciesSelect">
              <option>Loading...</option>
            </select>
          </label>
        </div>
        <div>
          <label>Planting Date
            <input type="date" name="planting_date" value="<?php echo date('Y-m-d'); ?>">
          </label>
        </div>
        <div>
          <label>Health Status
            <select name="health_status">
              <option value="healthy" selected>Healthy</option>
              <option value="fair">Fair</option>
              <option value="critical">Critical</option>
            </select>
          </label>
        </div>
        <div>
          <label>Location (GPS)
            <input type="text" name="location" placeholder="Lat, Lng">
            <span class="muted small">Use device GPS or map pin (offline capable when implemented).</span>
          </label>
        </div>
      </div>
      <div class="actions">
        <button class="btn alt" type="submit" disabled>Save (Coming Soon)</button>
      </div>
    </form>
  </div>

  <div class="tree-list">
    <h3>Adopt a Tree</h3>
    <?php if (empty($adoptable_trees)): ?>
      <p class="muted">No adoptable trees listed yet.</p>
    <?php else: ?>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Tree ID</th>
              <th>Species</th>
              <th>Age</th>
              <th>Location</th>
              <th>Health</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($adoptable_trees as $t): ?>
              <tr>
                <td><?php echo htmlspecialchars($t['id']); ?></td>
                <td><?php echo htmlspecialchars($t['species']); ?></td>
                <td><?php echo htmlspecialchars($t['age']); ?></td>
                <td><?php echo htmlspecialchars($t['lat'] . ', ' . $t['lng']); ?></td>
                <td class="status-<?php echo htmlspecialchars($t['health']); ?>"><?php echo htmlspecialchars($t['health']); ?></td>
                <td><button class="btn secondary" disabled>Adopt (Coming Soon)</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', async function(){
    try {
      const select = document.getElementById('speciesSelect');
      if (window.MitiHubSchool) await window.MitiHubSchool.loadSpecies(select);
    } catch (e) { /* noop */ }
  });
</script>
<?php include '_footer.php'; ?>