<?php
/**
 * Delete Student Confirmation Page (Module 6)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    set_flash('error', 'Invalid student ID requested.');
    redirect('students/index.php');
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT id, full_name, roll_number, course, email FROM students WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('error', 'Student record not found.');
    redirect('students/index.php');
}

$pageTitle = 'Delete Student';
$activePage = 'students';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card border-danger shadow-sm">
      <div class="card-header bg-danger text-white py-3">
        <h2 class="h6 fw-bold mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Student Deletion</h2>
      </div>
      <div class="card-body p-4">
        <p>You are about to permanently delete the following student record and any associated uploaded photograph:</p>
        <ul class="list-group mb-4">
          <li class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Name</span>
            <strong><?= e($student['full_name']) ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Roll Number</span>
            <code><?= e($student['roll_number']) ?></code>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Course</span>
            <span><?= e($student['course']) ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span class="text-muted">Email</span>
            <span><?= e($student['email']) ?></span>
          </li>
        </ul>

        <form method="POST" action="<?= e(url('actions/destroy.php')) ?>" class="d-flex justify-content-end gap-2">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string) $student['id']) ?>">
          <a href="<?= e(url('students/index.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-trash me-1"></i>Yes, Delete Permanently
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
