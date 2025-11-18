<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

$page_title = 'QR Scanner';
$current_page = 'qr';
include '_header.php';
?>

<!-- QR Code Scanner -->
<section class="card">
  <h2 class="section-title">QR Code Scanner</h2>
  <div id="qr-reader" class="qr-container" style="min-height:280px;background:#f8fafc;border:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;">Loading scanner...</div>
  <div class="muted small" id="qr-result"></div>
  <p class="muted small">Scan a tree's QR to view its profile. Offline capable once cached.</p>
</section>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.MitiHubSchool && window.MitiHubSchool.initQrScanner) {
      window.MitiHubSchool.initQrScanner('qr-reader', function(text){
        const out = document.getElementById('qr-result');
        out.textContent = 'Scanned: ' + text;
        // TODO: fetch tree profile and display (cache offline)
      });
    }
  });
</script>
<?php include '_footer.php'; ?>