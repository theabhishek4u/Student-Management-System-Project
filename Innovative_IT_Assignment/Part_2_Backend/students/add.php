<?php
/**
 * Student Registration Form (Module 2)
 */
require_once __DIR__ . '/../includes/helpers.php';

$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$pageTitle = 'Register Student';
$activePage = 'add_student';
include __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h6 fw-bold mb-0">Student Registration Form</h2>
          <small class="text-muted">All fields marked with * are required. Server-side validation is enforced.</small>
        </div>
        <a href="<?= e(url('students/index.php')) ?>" class="btn btn-sm btn-outline-secondary">Back to List</a>
      </div>
      <div class="card-body p-4">
        <form method="POST" action="<?= e(url('actions/store.php')) ?>" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="full_name" class="form-label fw-medium">Full Name *</label>
              <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                value="<?= e($old['full_name'] ?? '') ?>"
                placeholder="e.g. Aarav Sharma"
                required
              >
              <?php if (isset($errors['full_name'])): ?>
                <div class="invalid-feedback"><?= e($errors['full_name']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label for="roll_number" class="form-label fw-medium">Roll Number *</label>
              <input
                type="text"
                id="roll_number"
                name="roll_number"
                class="form-control <?= isset($errors['roll_number']) ? 'is-invalid' : '' ?>"
                value="<?= e($old['roll_number'] ?? '') ?>"
                placeholder="e.g. BCA2025099"
                required
              >
              <?php if (isset($errors['roll_number'])): ?>
                <div class="invalid-feedback"><?= e($errors['roll_number']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label fw-medium">Email Address *</label>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                value="<?= e($old['email'] ?? '') ?>"
                placeholder="student@example.edu"
                required
              >
              <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= e($errors['email']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label for="phone" class="form-label fw-medium">Phone Number *</label>
              <input
                type="text"
                id="phone"
                name="phone"
                class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                value="<?= e($old['phone'] ?? '') ?>"
                placeholder="+91 98000 10101"
                required
              >
              <?php if (isset($errors['phone'])): ?>
                <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label for="gender" class="form-label fw-medium">Gender *</label>
              <select id="gender" name="gender" class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" required>
                <option value="">Select Gender</option>
                <?php foreach (allowed_genders() as $g): ?>
                  <option value="<?= e($g) ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['gender'])): ?>
                <div class="invalid-feedback"><?= e($errors['gender']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label for="course" class="form-label fw-medium">Course *</label>
              <select id="course" name="course" class="form-select <?= isset($errors['course']) ? 'is-invalid' : '' ?>" required>
                <option value="">Select Course</option>
                <?php foreach (allowed_courses() as $c): ?>
                  <option value="<?= e($c) ?>" <?= ($old['course'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['course'])): ?>
                <div class="invalid-feedback"><?= e($errors['course']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-4">
              <label for="date_of_birth" class="form-label fw-medium">Date of Birth *</label>
              <input
                type="date"
                id="date_of_birth"
                name="date_of_birth"
                class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>"
                value="<?= e($old['date_of_birth'] ?? '') ?>"
                required
              >
              <?php if (isset($errors['date_of_birth'])): ?>
                <div class="invalid-feedback"><?= e($errors['date_of_birth']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <label for="address" class="form-label fw-medium">Address *</label>
              <textarea
                id="address"
                name="address"
                rows="3"
                class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                placeholder="Enter full residential address"
                required
              ><?= e($old['address'] ?? '') ?></textarea>
              <?php if (isset($errors['address'])): ?>
                <div class="invalid-feedback"><?= e($errors['address']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-12">
              <label for="profile_photo" class="form-label fw-medium">Profile Photo (JPG, JPEG, PNG, WEBP &mdash; Max 2 MB)</label>
              <input
                type="file"
                id="profile_photo"
                name="profile_photo"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="form-control <?= isset($errors['profile_photo']) ? 'is-invalid' : '' ?>"
              >
              <?php if (isset($errors['profile_photo'])): ?>
                <div class="invalid-feedback"><?= e($errors['profile_photo']) ?></div>
              <?php endif; ?>
              <img id="photoPreviewImg" src="#" alt="Preview" class="student-photo-lg mt-3 d-none">
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="<?= e(url('students/index.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Register Student
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
