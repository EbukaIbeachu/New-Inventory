<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // If database doesn't exist, we might be in installation mode
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        // Allow connection without db name to create it
        try {
            $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $pdo = new PDO($dsn_no_db, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e2) {
            error_log('DB connect (no DB) failed: ' . $e2->getMessage());
            die("Database connection error. Please try again later.");
        }
    } else {
        error_log('DB connect failed: ' . $e->getMessage());
        die("Database connection error. Please try again later.");
    }
}
