<?php
/**
 * Reusable Top Header & Layout Opener
 * Expects $pageTitle (string) and $activePage (string) to be defined before include.
 */
require_once __DIR__ . '/helpers.php';
$pageTitle = $pageTitle ?? 'Smart Student Management System';
$activePage = $activePage ?? 'dashboard';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Smart Student Management System</title>
  <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/images/logo.svg')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&family=Geist:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
  <div class="app-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="app-main">
      <header class="topbar">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarToggleBtn" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
          </button>
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted small d-none d-sm-inline">Smart SMS</span>
            <span class="text-muted small d-none d-sm-inline">/</span>
            <h1 class="topbar-title mb-0"><?= e($pageTitle) ?></h1>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="topbar-status-pill d-none d-md-inline-flex">
            <span class="status-dot"></span>MySQL PDO
          </span>
          <a href="<?= e(url('students/index.php')) ?>" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-search"></i>Search
          </a>
          <a href="<?= e(url('students/add.php')) ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Student
          </a>
        </div>
      </header>

      <main class="content-area">
        <?php if ($flash): ?>
          <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>
