<?php
/**
 * Store New Student Record (Create Action)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/validation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('students/add.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid security token. Please try submitting the form again.');
    redirect('students/add.php');
}

$pdo = getPDO();
[$cleanData, $errors] = validate_student_input($pdo, $_POST);

// Validate & handle optional profile photo upload
$uploadedPhoto = null;
$uploadError = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadedPhoto = handle_photo_upload($_FILES['profile_photo'], $uploadError);
    if ($uploadError !== null) {
        $errors['profile_photo'] = $uploadError;
    }
}

if (!empty($errors)) {
    // Clean up newly uploaded photo if other form fields failed validation
    if ($uploadedPhoto !== null) {
        delete_student_photo($uploadedPhoto);
    }
    $_SESSION['old'] = $_POST;
    $_SESSION['errors'] = $errors;
    set_flash('error', 'Please fix the highlighted validation errors below.');
    redirect('students/add.php');
}

try {
    $sql = 'INSERT INTO students
            (full_name, roll_number, email, phone, gender, course, cgpa, date_of_birth, address, profile_photo)
            VALUES
            (:full_name, :roll_number, :email, :phone, :gender, :course, :cgpa, :date_of_birth, :address, :profile_photo)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'     => $cleanData['full_name'],
        ':roll_number'   => $cleanData['roll_number'],
        ':email'         => $cleanData['email'],
        ':phone'         => $cleanData['phone'],
        ':gender'        => $cleanData['gender'],
        ':course'        => $cleanData['course'],
        ':cgpa'          => $cleanData['cgpa'],
        ':date_of_birth' => $cleanData['date_of_birth'],
        ':address'       => $cleanData['address'],
        ':profile_photo' => $uploadedPhoto,
    ]);

    log_activity($pdo, 'REGISTER', 'Registered ' . $cleanData['full_name'] . ' (' . $cleanData['roll_number'] . ') in ' . $cleanData['course']);
    set_flash('success', 'Student "' . $cleanData['full_name'] . '" registered successfully.');
    redirect('students/index.php');
} catch (PDOException $e) {
    if ($uploadedPhoto !== null) {
        delete_student_photo($uploadedPhoto);
    }
    set_flash('error', 'Database error while saving student record.');
    redirect('students/add.php');
}
