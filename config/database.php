<?php
/**
 * Database connection (PDO)
 * -------------------------
 * Stage: 2 - Database
 *
 * Update the credentials below to match your local MySQL/phpMyAdmin setup
 * (default XAMPP/LAMPP credentials are usually db_user = "root", db_pass = "").
 */

$db_host = 'localhost';
$db_name = 'ecommerce';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In production, never expose $e->getMessage() to the user.
    die('Database connection failed: ' . $e->getMessage());
}
