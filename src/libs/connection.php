<?php

$host   = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$port   = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$user   = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass   = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'vs_new';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Greška pri konekciji: ' . $e->getMessage());
}
?>