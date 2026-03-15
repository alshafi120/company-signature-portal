<?php
/**
 * Database Connection Handler
 */

require_once __DIR__ . '/../config/app.php';

// Load database config
$dbConfigFile = __DIR__ . '/../config/database.php';
if (!file_exists($dbConfigFile)) {
    die('Database configuration file not found. Please copy config/database.example.php to config/database.php and update credentials.');
}
require_once $dbConfigFile;

/**
 * Get PDO database connection
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }

    return $pdo;
}
