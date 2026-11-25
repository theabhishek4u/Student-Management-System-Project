<?php
/**
 * Reusable Footer & Script Includes
 */
?>
      </main>

      <footer class="app-footer">
        <div class="d-flex align-items-center gap-2">
          <span class="fw-semibold text-dark">Smart SMS</span>
          <span>&middot;</span>
          <span>PHP &amp; MySQL (PDO) CRUD System</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <a href="<?= e(url('index.php')) ?>" class="footer-nav-link">Dashboard</a>
          <a href="<?= e(url('students/index.php')) ?>" class="footer-nav-link">Directory</a>
          <a href="<?= e(url('students/add.php')) ?>" class="footer-nav-link">Register</a>
        </div>
      </footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= e(url('assets/js/script.js')) ?>"></script>
</body>
</html>
