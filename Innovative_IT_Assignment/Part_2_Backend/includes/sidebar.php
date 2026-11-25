<?php
/**
 * Reusable Sidebar Navigation
 */
?>
<aside class="sidebar" id="appSidebar">
  <div class="sidebar-brand">
    <img src="<?= e(url('assets/images/logo.svg')) ?>" alt="Smart SMS Logo" width="34" height="34" class="rounded-2 flex-shrink-0">
    <div>
      <div class="fw-bold text-white lh-sm">Smart SMS</div>
      <small class="text-white-50">Admin Dashboard</small>
    </div>
  </div>

  <nav class="sidebar-nav" aria-label="Sidebar navigation">
    <a href="<?= e(url('index.php')) ?>" class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>
    <a href="<?= e(url('students/index.php')) ?>" class="sidebar-link <?= $activePage === 'students' ? 'active' : '' ?>">
      <i class="bi bi-people"></i>
      <span>All Students</span>
    </a>
    <a href="<?= e(url('students/add.php')) ?>" class="sidebar-link <?= $activePage === 'add_student' ? 'active' : '' ?>">
      <i class="bi bi-person-plus"></i>
      <span>Register Student</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="small text-white-50">Environment</div>
    <div class="small text-white fw-medium">XAMPP &middot; MySQL PDO</div>
  </div>
</aside>
<div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop"></div>
