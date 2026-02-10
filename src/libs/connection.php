<?php

declare(strict_types=1);

$host   = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$port   = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$user   = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass   = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'egoire';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

try {
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,  // Use real prepared statements
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]);
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    http_response_code(503);
    die('Servis je trenutno nedostupan. Pokušajte ponovo kasnije.');
}
