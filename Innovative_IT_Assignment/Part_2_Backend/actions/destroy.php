<?php
/**
 * Delete Student Record & Associated Photo (Delete Action)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('students/index.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid security token. Deletion aborted.');
    redirect('students/index.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    set_flash('error', 'Invalid student ID.');
    redirect('students/index.php');
}

$pdo = getPDO();

try {
    $stmt = $pdo->prepare('SELECT full_name, profile_photo FROM students WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $student = $stmt->fetch();

    if (!$student) {
        set_flash('error', 'Student record not found or already deleted.');
        redirect('students/index.php');
    }

    $delStmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
    $delStmt->execute([':id' => $id]);

    // Remove associated uploaded photo from disk
    delete_student_photo($student['profile_photo']);

    set_flash('success', 'Student "' . $student['full_name'] . '" and associated files deleted successfully.');
    redirect('students/index.php');
} catch (PDOException $e) {
    set_flash('error', 'Database error while deleting student record.');
    redirect('students/index.php');
}
