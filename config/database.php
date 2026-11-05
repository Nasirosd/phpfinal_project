<?php
declare(strict_types=1);

$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDirectory = dirname($scriptPath);
if (in_array(basename($scriptDirectory), ['admin', 'user', 'booking', 'payment', 'feedback'], true)) {
    $scriptDirectory = dirname($scriptDirectory);
}
define('APP_BASE_URL', rtrim($scriptDirectory, '/'));
define('APP_NAME', 'LuxStay');

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: 'localhost';
    $name = getenv('DB_NAME') ?: 'luxstay_hotel_db';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$name};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        error_log('LuxStay database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('Unable to connect to the database. Import database/luxstay_hotel_db.sql and check config/database.php.');
    }

    return $pdo;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE_URL ?: '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
