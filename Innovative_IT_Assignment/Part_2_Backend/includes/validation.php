<?php
/**
 * Server-Side Student Input Validation
 * Enforces all required fields, formats, allowed values, and unique roll number.
 */

require_once __DIR__ . '/helpers.php';

/**
 * Validate student form input and return [$cleanData, $errors].
 */
function validate_student_input(PDO $pdo, array $input, ?int $excludeId = null): array
{
    $errors = [];

    $fullName    = trim((string) ($input['full_name'] ?? ''));
    $rollNumber  = strtoupper(trim((string) ($input['roll_number'] ?? '')));
    $email       = trim((string) ($input['email'] ?? ''));
    $phone       = trim((string) ($input['phone'] ?? ''));
    $gender      = trim((string) ($input['gender'] ?? ''));
    $course      = trim((string) ($input['course'] ?? ''));
    $dateOfBirth = trim((string) ($input['date_of_birth'] ?? ''));
    $address     = trim((string) ($input['address'] ?? ''));

    // Full Name
    if ($fullName === '') {
        $errors['full_name'] = 'Full name is required.';
    } elseif (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
        $errors['full_name'] = 'Full name must be between 2 and 120 characters.';
    }

    // Roll Number (required, format, unique)
    if ($rollNumber === '') {
        $errors['roll_number'] = 'Roll number is required.';
    } elseif (!preg_match('/^[A-Z0-9_-]{2,30}$/', $rollNumber)) {
        $errors['roll_number'] = 'Roll number must be 2-30 characters (letters, numbers, hyphens, or underscores).';
    } else {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT id FROM students WHERE roll_number = :roll AND id != :id LIMIT 1');
            $stmt->execute([':roll' => $rollNumber, ':id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM students WHERE roll_number = :roll LIMIT 1');
            $stmt->execute([':roll' => $rollNumber]);
        }
        if ($stmt->fetch()) {
            $errors['roll_number'] = 'This roll number is already registered to another student.';
        }
    }

    // Email
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Phone
    $digitsOnly = preg_replace('/\D/', '', $phone);
    if ($phone === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone) || strlen($digitsOnly) < 7 || strlen($digitsOnly) > 15) {
        $errors['phone'] = 'Please enter a valid phone number (7 to 15 digits).';
    }

    // Gender
    if ($gender === '' || !in_array($gender, allowed_genders(), true)) {
        $errors['gender'] = 'Please select a valid gender.';
    }

    // Course
    if ($course === '' || !in_array($course, allowed_courses(), true)) {
        $errors['course'] = 'Please select a valid course.';
    }

    // Date of Birth
    if ($dateOfBirth === '') {
        $errors['date_of_birth'] = 'Date of birth is required.';
    } else {
        $dobObj = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        $dobErrors = DateTime::getLastErrors();
        $hasParseErrors = is_array($dobErrors) && ($dobErrors['warning_count'] > 0 || $dobErrors['error_count'] > 0);

        if (!$dobObj || $hasParseErrors || $dobObj->format('Y-m-d') !== $dateOfBirth) {
            $errors['date_of_birth'] = 'Please enter a valid date in YYYY-MM-DD format.';
        } else {
            $today = new DateTime('today');
            $minDob = new DateTime('1920-01-01');
            if ($dobObj >= $today) {
                $errors['date_of_birth'] = 'Date of birth must be in the past.';
            } elseif ($dobObj < $minDob) {
                $errors['date_of_birth'] = 'Please enter a realistic date of birth.';
            }
        }
    }

    // Address
    if ($address === '') {
        $errors['address'] = 'Address is required.';
    } elseif (mb_strlen($address) < 5 || mb_strlen($address) > 500) {
        $errors['address'] = 'Address must be between 5 and 500 characters.';
    }

    $cleanData = [
        'full_name'     => $fullName,
        'roll_number'   => $rollNumber,
        'email'         => $email,
        'phone'         => $phone,
        'gender'        => $gender,
        'course'        => $course,
        'date_of_birth' => $dateOfBirth,
        'address'       => $address,
    ];

    return [$cleanData, $errors];
}
