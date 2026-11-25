<?php
/**
 * Update Existing Student Record (Update Action)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/validation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('students/index.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    set_flash('error', 'Invalid student ID.');
    redirect('students/index.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid security token. Please try submitting the form again.');
    redirect('students/edit.php?id=' . $id);
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$existing = $stmt->fetch();

if (!$existing) {
    set_flash('error', 'Student record not found.');
    redirect('students/index.php');
}

[$cleanData, $errors] = validate_student_input($pdo, $_POST, $id);

// Handle optional replacement photo
$newPhoto = null;
$uploadError = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $newPhoto = handle_photo_upload($_FILES['profile_photo'], $uploadError);
    if ($uploadError !== null) {
        $errors['profile_photo'] = $uploadError;
    }
}

if (!empty($errors)) {
    if ($newPhoto !== null) {
        delete_student_photo($newPhoto);
    }
    $_SESSION['old'] = $_POST;
    $_SESSION['errors'] = $errors;
    set_flash('error', 'Please fix the highlighted validation errors below.');
    redirect('students/edit.php?id=' . $id);
}

$finalPhoto = $newPhoto !== null ? $newPhoto : $existing['profile_photo'];

try {
    $sql = 'UPDATE students
            SET full_name = :full_name,
                roll_number = :roll_number,
                email = :email,
                phone = :phone,
                gender = :gender,
                course = :course,
                cgpa = :cgpa,
                date_of_birth = :date_of_birth,
                address = :address,
                profile_photo = :profile_photo
            WHERE id = :id';
    $updateStmt = $pdo->prepare($sql);
    $updateStmt->execute([
        ':full_name'     => $cleanData['full_name'],
        ':roll_number'   => $cleanData['roll_number'],
        ':email'         => $cleanData['email'],
        ':phone'         => $cleanData['phone'],
        ':gender'        => $cleanData['gender'],
        ':course'        => $cleanData['course'],
        ':cgpa'          => $cleanData['cgpa'],
        ':date_of_birth' => $cleanData['date_of_birth'],
        ':address'       => $cleanData['address'],
        ':profile_photo' => $finalPhoto,
        ':id'            => $id,
    ]);

    // Delete old image only after DB update succeeds
    if ($newPhoto !== null && !empty($existing['profile_photo'])) {
        delete_student_photo($existing['profile_photo']);
    }

    log_activity($pdo, 'UPDATE', 'Updated profile for ' . $cleanData['full_name'] . ' (' . $cleanData['roll_number'] . ')');
    set_flash('success', 'Student "' . $cleanData['full_name'] . '" updated successfully.');
    redirect('students/view.php?id=' . $id);
} catch (PDOException $e) {
    if ($newPhoto !== null) {
        delete_student_photo($newPhoto);
    }
    set_flash('error', 'Database error while updating student record.');
    redirect('students/edit.php?id=' . $id);
}
