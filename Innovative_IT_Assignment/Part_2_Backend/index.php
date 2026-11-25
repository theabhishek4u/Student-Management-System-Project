<?php
/**
 * Administrator Dashboard (Module 1)
 * Retrieves all statistics dynamically from MySQL via PDO.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = getPDO();

// Dynamic statistics from MySQL
$totalStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalCourses  = (int) $pdo->query('SELECT COUNT(DISTINCT course) FROM students')->fetchColumn();
$latestAdded   = $pdo->query('SELECT created_at FROM students ORDER BY created_at DESC LIMIT 1')->fetchColumn();

$recentStudents = $pdo->query(
    'SELECT id, full_name, roll_number, course, email, profile_photo, created_at
     FROM students
     ORDER BY created_at DESC, id DESC
     LIMIT 5'
)->fetchAll();

$courseStats = $pdo->query(
    'SELECT course, COUNT(*) AS student_count
     FROM students
     GROUP BY course
     ORDER BY student_count DESC, course ASC'
)->fetchAll();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-4">
    <div class="stat-card-bs d-flex align-items-center gap-3">
      <div class="stat-icon-box bg-primary-subtle text-primary">
        <i class="bi bi-people-fill"></i>
      </div>
      <div>
        <div class="text-muted small fw-medium">Total Students</div>
        <div class="fs-3 fw-bold"><?= e((string) $totalStudents) ?></div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-4">
    <div class="stat-card-bs d-flex align-items-center gap-3">
      <div class="stat-icon-box bg-success-subtle text-success">
        <i class="bi bi-book-fill"></i>
      </div>
      <div>
        <div class="text-muted small fw-medium">Active Courses</div>
        <div class="fs-3 fw-bold"><?= e((string) $totalCourses) ?></div>
      </div>
    </div>
  </div>

  <div class="col-sm-12 col-xl-4">
    <div class="stat-card-bs d-flex align-items-center gap-3">
      <div class="stat-icon-box bg-warning-subtle text-warning">
        <i class="bi bi-calendar-check-fill"></i>
      </div>
      <div>
        <div class="text-muted small fw-medium">Latest Registration</div>
        <div class="fs-6 fw-bold">
          <?= $latestAdded ? e(date('d M Y, h:i A', strtotime($latestAdded))) : 'No records yet' ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <div>
          <h2 class="h6 fw-bold mb-0">Recently Added Students</h2>
          <small class="text-muted">Latest 5 student registrations from MySQL</small>
        </div>
        <a href="<?= e(url('students/index.php')) ?>" class="btn btn-outline-primary btn-sm">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentStudents)): ?>
          <div class="p-4 text-center text-muted">
            No students found. <a href="<?= e(url('students/add.php')) ?>">Register the first student</a>.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Student</th>
                  <th>Roll Number</th>
                  <th>Course</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentStudents as $student): ?>
                  <?php
                    $initials = strtoupper(substr($student['full_name'], 0, 1));
                    $photoPath = !empty($student['profile_photo'])
                        ? url('uploads/students/' . basename($student['profile_photo']))
                        : null;
                  ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <?php if ($photoPath): ?>
                          <img src="<?= e($photoPath) ?>" alt="<?= e($student['full_name']) ?>" class="student-thumb">
                        <?php else: ?>
                          <span class="student-initials"><?= e($initials) ?></span>
                        <?php endif; ?>
                        <div>
                          <div class="fw-semibold"><?= e($student['full_name']) ?></div>
                          <small class="text-muted"><?= e($student['email']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td><code><?= e($student['roll_number']) ?></code></td>
                    <td><span class="badge text-bg-primary-subtle text-primary border border-primary-subtle"><?= e($student['course']) ?></span></td>
                    <td class="text-end">
                      <a href="<?= e(url('students/view.php?id=' . (int) $student['id'])) ?>" class="btn btn-sm btn-outline-secondary">
                        View
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h2 class="h6 fw-bold mb-0">Course Distribution</h2>
      </div>
      <div class="card-body">
        <?php if (empty($courseStats)): ?>
          <p class="text-muted mb-0">No course data available.</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($courseStats as $row): ?>
              <?php $pct = $totalStudents > 0 ? round(((int) $row['student_count'] / $totalStudents) * 100) : 0; ?>
              <div>
                <div class="d-flex justify-content-between small fw-medium mb-1">
                  <span><?= e($row['course']) ?></span>
                  <span><?= e((string) $row['student_count']) ?> (<?= e((string) $pct) ?>%)</span>
                </div>
                <div class="progress" style="height: 7px;">
                  <div class="progress-bar bg-primary" style="width: <?= e((string) $pct) ?>%;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <h2 class="h6 fw-bold mb-0">Quick Actions</h2>
      </div>
      <div class="card-body d-grid gap-2">
        <a href="<?= e(url('students/add.php')) ?>" class="btn btn-primary">
          <i class="bi bi-person-plus me-1"></i>Register New Student
        </a>
        <a href="<?= e(url('students/index.php')) ?>" class="btn btn-outline-secondary">
          <i class="bi bi-table me-1"></i>Manage Student Directory
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
