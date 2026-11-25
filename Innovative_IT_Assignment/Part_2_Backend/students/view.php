<?php
/**
 * Student Details Page (Module 4)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    set_flash('error', 'Invalid student ID requested.');
    redirect('students/index.php');
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('error', 'Student record not found.');
    redirect('students/index.php');
}

$initials = strtoupper(substr($student['full_name'], 0, 1));
$photoUrl = !empty($student['profile_photo'])
    ? url('uploads/students/' . basename($student['profile_photo']))
    : null;

$pageTitle = 'Student Details';
$activePage = 'students';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="h6 fw-bold mb-0">Student Profile #<?= e((string) $student['id']) ?></h2>
          <small class="text-muted">Complete academic and contact record</small>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= e(url('students/edit.php?id=' . (int) $student['id'])) ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil me-1"></i>Edit
          </a>
          <a href="<?= e(url('students/delete.php?id=' . (int) $student['id'])) ?>" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-trash me-1"></i>Delete
          </a>
          <a href="<?= e(url('students/index.php')) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to List
          </a>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4 pb-4 mb-4 border-bottom">
          <?php if ($photoUrl): ?>
            <img src="<?= e($photoUrl) ?>" alt="<?= e($student['full_name']) ?>" class="student-photo-lg">
          <?php else: ?>
            <div class="student-initials-lg"><?= e($initials) ?></div>
          <?php endif; ?>

          <div>
            <span class="badge text-bg-primary-subtle text-primary border border-primary-subtle mb-2">
              <?= e($student['course']) ?>
            </span>
            <h3 class="h4 fw-bold mb-1"><?= e($student['full_name']) ?></h3>
            <p class="text-muted mb-0">Roll Number: <code><?= e($student['roll_number']) ?></code></p>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6">
            <div class="p-3 bg-light rounded border h-100">
              <h4 class="h6 fw-bold text-uppercase text-muted small mb-3">Personal &amp; Academic Details</h4>
              <dl class="row mb-0">
                <dt class="col-sm-5 text-muted">Full Name</dt>
                <dd class="col-sm-7 fw-medium"><?= e($student['full_name']) ?></dd>

                <dt class="col-sm-5 text-muted">Roll Number</dt>
                <dd class="col-sm-7"><code><?= e($student['roll_number']) ?></code></dd>

                <dt class="col-sm-5 text-muted">Course</dt>
                <dd class="col-sm-7"><?= e($student['course']) ?></dd>

                <dt class="col-sm-5 text-muted">CGPA</dt>
                <dd class="col-sm-7 fw-semibold"><?= e(number_format((float) ($student['cgpa'] ?? 0), 2)) ?> / 10</dd>

                <dt class="col-sm-5 text-muted">Gender</dt>
                <dd class="col-sm-7"><?= e($student['gender']) ?></dd>

                <dt class="col-sm-5 text-muted">Date of Birth</dt>
                <dd class="col-sm-7"><?= e($student['date_of_birth']) ?></dd>
              </dl>
            </div>
          </div>

          <div class="col-md-6">
            <div class="p-3 bg-light rounded border h-100">
              <h4 class="h6 fw-bold text-uppercase text-muted small mb-3">Contact &amp; Record Metadata</h4>
              <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8 text-break"><?= e($student['email']) ?></dd>

                <dt class="col-sm-4 text-muted">Phone</dt>
                <dd class="col-sm-8"><?= e($student['phone']) ?></dd>

                <dt class="col-sm-4 text-muted">Address</dt>
                <dd class="col-sm-8"><?= e($student['address']) ?></dd>

                <dt class="col-sm-4 text-muted">Created</dt>
                <dd class="col-sm-8"><?= e(date('d M Y, h:i A', strtotime($student['created_at']))) ?></dd>

                <dt class="col-sm-4 text-muted">Updated</dt>
                <dd class="col-sm-8"><?= e(date('d M Y, h:i A', strtotime($student['updated_at']))) ?></dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
