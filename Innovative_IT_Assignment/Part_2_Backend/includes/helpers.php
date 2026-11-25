<?php
/**
 * Shared Helper Functions: Escaping, URLs, Flash Messages, CSRF, and File Handling.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape HTML output to prevent XSS.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Compute the base URL path to Part_2_Backend regardless of subfolder depth.
 */
function app_base_url(): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $scriptDir = rtrim($scriptDir, '/');

    foreach (['/students', '/actions', '/config', '/includes'] as $subdir) {
        $len = strlen($subdir);
        if (strlen($scriptDir) >= $len && substr($scriptDir, -$len) === $subdir) {
            return substr($scriptDir, 0, -$len);
        }
    }

    return $scriptDir;
}

/**
 * Build an application URL relative to Part_2_Backend.
 */
function url(string $path = ''): string
{
    $base = app_base_url();
    $cleanPath = ltrim($path, '/');
    if ($cleanPath === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $cleanPath;
}

/**
 * Redirect to a relative application path and exit.
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Store a flash notification in session.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear the current flash notification.
 */
function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Generate or return the session CSRF token.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify submitted CSRF token.
 */
function verify_csrf(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Allowed course options.
 */
function allowed_courses(): array
{
    return ['BCA', 'MCA', 'BBA', 'B.Tech', 'MBA'];
}

/**
 * Allowed gender options.
 */
function allowed_genders(): array
{
    return ['Male', 'Female', 'Other'];
}

/**
 * Absolute path to the student photo uploads directory.
 */
function student_upload_dir(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'students';
}

/**
 * Ensure the uploads directory exists (CREATE operation in file handling).
 */
function ensure_upload_dir(): bool
{
    $dir = student_upload_dir();
    if (!is_dir($dir)) {
        return mkdir($dir, 0755, true);
    }
    return is_writable($dir);
}

/**
 * Validate and save an uploaded student profile photo.
 * Returns the generated filename on success, or null if no file was uploaded.
 * Populates $error on validation or move failure.
 */
function handle_photo_upload(array $file, ?string &$error): ?string
{
    $error = null;

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'Uploaded file exceeds server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'Uploaded file exceeds maximum form size.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write uploaded file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        $error = $uploadErrors[$file['error']] ?? 'Unknown file upload error.';
        return null;
    }

    $maxBytes = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $maxBytes) {
        $error = 'Profile photo must be 2 MB or smaller.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExtensions, true)) {
        $error = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.';
        return null;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid upload source.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedMimes[$mime])) {
        $error = 'File content does not match a valid image MIME type (JPG, PNG, WEBP).';
        return null;
    }

    if (!ensure_upload_dir()) {
        $error = 'Upload directory is not writable.';
        return null;
    }

    $safeFilename = 'student_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
    $destination = student_upload_dir() . DIRECTORY_SEPARATOR . $safeFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Could not save the uploaded profile photo.';
        return null;
    }

    return $safeFilename;
}

/**
 * Delete a stored student photo safely (DELETE operation in file handling).
 */
function delete_student_photo(?string $filename): void
{
    if (empty($filename)) {
        return;
    }
    $safeName = basename($filename);
    $fullPath = student_upload_dir() . DIRECTORY_SEPARATOR . $safeName;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

/**
 * Record an admin action in the activity_logs table.
 */
function log_activity(PDO $pdo, string $action, string $description): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO activity_logs (action, description) VALUES (:action, :description)');
        $stmt->execute([
            ':action'      => $action,
            ':description' => $description,
        ]);
    } catch (PDOException $e) {
        // Do not block primary CRUD operation if log table is unavailable
    }
}
