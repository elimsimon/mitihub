    </main>
  </div>

  <script>
    (function(){
      const sidebar = document.getElementById('sidebar');
      const toggle = document.getElementById('sidebarToggle');
      toggle.addEventListener('click', function(){ sidebar.classList.toggle('open'); });
      // Close sidebar when a nav link is clicked (mobile UX)
      document.querySelectorAll('.nav a').forEach(function(a){
        a.addEventListener('click', function(){ if (window.innerWidth <= 900) sidebar.classList.remove('open'); });
      });
    })();
  </script>
  <script>
    // Register Service Worker
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function(){
        navigator.serviceWorker.register('<?php echo base_url('assets/js/sw.js'); ?>');
      });
    }
  </script>
  <script src="<?php echo base_url('assets/js/school.js'); ?>"></script>
  <script src="<?php echo base_url('assets/js/mitibot.js'); ?>" defer></script>
</body>
</html>