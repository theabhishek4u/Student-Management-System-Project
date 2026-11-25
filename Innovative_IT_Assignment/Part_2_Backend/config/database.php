<?php
/**
 * Centralized PDO Database Connection
 * Uses exception mode, UTF-8 (utf8mb4), and native prepared statements.
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'student_management_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance.
 *
 * @return PDO
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(
            '<div style="font-family:Segoe UI,Arial,sans-serif;max-width:640px;margin:40px auto;padding:24px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;">'
            . '<h2 style="margin-top:0;">Database Connection Error</h2>'
            . '<p>Could not connect to MySQL database <strong>' . htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
            . '<p style="font-size:14px;">Please ensure Apache &amp; MySQL are running in XAMPP and <code>database.sql</code> has been imported.</p>'
            . '</div>'
        );
    }
}
