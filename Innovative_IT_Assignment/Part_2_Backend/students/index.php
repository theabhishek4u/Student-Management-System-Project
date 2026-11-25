<?php
/**
 * Student Listing (Module 3)
 * Supports search, course filtering, pagination, and delete modal confirmation.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getPDO();

$search = trim((string) ($_GET['search'] ?? ''));
$course = trim((string) ($_GET['course'] ?? ''));
$page   = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = '(full_name LIKE :search_name OR roll_number LIKE :search_roll OR email LIKE :search_email)';
    $like = '%' . $search . '%';
    $params[':search_name']  = $like;
    $params[':search_roll']  = $like;
    $params[':search_email'] = $like;
}

if ($course !== '' && in_array($course, allowed_courses(), true)) {
    $conditions[] = 'course = :course';
    $params[':course'] = $course;
} else {
    $course = '';
}

$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

// Count total matching records
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRecords / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Fetch paginated records
$listSql = "SELECT id, full_name, roll_number, email, phone, course, profile_photo, created_at
            FROM students
            {$whereSql}
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset";
$listStmt = $pdo->prepare($listSql);
foreach ($params as $key => $val) {
    $listStmt->bindValue($key, $val, PDO::PARAM_STR);
}
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$students = $listStmt->fetchAll();

$pageTitle = 'Student Directory';
$activePage = 'students';
include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="<?= e(url('students/index.php')) ?>" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
          <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search by name, roll number, or email..."
            value="<?= e($search) ?>"
          >
        </div>
      </div>
      <div class="col-md-3">
        <select name="course" class="form-select">
          <option value="">All Courses</option>
          <?php foreach (allowed_courses() as $c): ?>
            <option value="<?= e($c) ?>" <?= $course === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
        <a href="<?= e(url('students/index.php')) ?>" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h2 class="h6 fw-bold mb-0">Registered Students</h2>
      <small class="text-muted">Showing <?= e((string) count($students)) ?> of <?= e((string) $totalRecords) ?> record(s)</small>
    </div>
    <a href="<?= e(url('students/add.php')) ?>" class="btn btn-sm btn-primary">
      <i class="bi bi-person-plus me-1"></i>Add New Student
    </a>
  </div>

  <?php if (empty($students)): ?>
    <div class="card-body text-center py-5">
      <i class="bi bi-inbox fs-1 text-muted"></i>
      <h3 class="h5 mt-3">No Student Records Found</h3>
      <p class="text-muted mb-3">Try clearing your search filters or register a new student.</p>
      <a href="<?= e(url('students/add.php')) ?>" class="btn btn-primary btn-sm">Register Student</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Photo</th>
            <th>ID</th>
            <th>Student Name</th>
            <th>Roll Number</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Course</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $student): ?>
            <?php
              $initials = strtoupper(substr($student['full_name'], 0, 1));
              $photoUrl = !empty($student['profile_photo'])
                  ? url('uploads/students/' . basename($student['profile_photo']))
                  : null;
            ?>
            <tr>
              <td>
                <?php if ($photoUrl): ?>
                  <img src="<?= e($photoUrl) ?>" alt="<?= e($student['full_name']) ?>" class="student-thumb">
                <?php else: ?>
                  <span class="student-initials"><?= e($initials) ?></span>
                <?php endif; ?>
              </td>
              <td>#<?= e((string) $student['id']) ?></td>
              <td class="fw-semibold"><?= e($student['full_name']) ?></td>
              <td><code><?= e($student['roll_number']) ?></code></td>
              <td><?= e($student['email']) ?></td>
              <td><?= e($student['phone']) ?></td>
              <td><span class="badge text-bg-primary-subtle text-primary border border-primary-subtle"><?= e($student['course']) ?></span></td>
              <td class="text-end text-nowrap">
                <a href="<?= e(url('students/view.php?id=' . (int) $student['id'])) ?>" class="btn btn-sm btn-outline-secondary" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= e(url('students/edit.php?id=' . (int) $student['id'])) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-danger"
                  title="Delete"
                  data-bs-toggle="modal"
                  data-bs-target="#deleteConfirmModal"
                  data-student-id="<?= e((string) $student['id']) ?>"
                  data-student-name="<?= e($student['full_name']) ?>"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="card-footer bg-white py-3">
        <nav aria-label="Student pagination">
          <ul class="pagination pagination-sm justify-content-end mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <?php
                $query = http_build_query([
                    'search' => $search,
                    'course' => $course,
                    'page'   => $p,
                ]);
              ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= e(url('students/index.php?' . $query)) ?>"><?= e((string) $p) ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= e(url('actions/destroy.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="deleteStudentIdInput" value="">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="deleteModalLabel">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Deletion
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Are you sure you want to permanently delete <strong id="deleteStudentNameText">this student</strong> and their uploaded profile photo?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete Student</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
