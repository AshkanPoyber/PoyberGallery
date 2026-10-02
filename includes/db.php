<?php

/**
 * PoyberGallery — Database Connection (PDO)
 */

declare(strict_types=1);

// Load environment variables from .env
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new RuntimeException(".env file not found at: $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Remove surrounding quotes
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
    }
}

loadEnv(__DIR__ . '/../.env');

// ---------- PDO Connection ----------
$host    = $_ENV['DB_HOST']    ?? 'localhost';
$dbname  = $_ENV['DB_NAME']    ?? 'poybergallery';
$user    = $_ENV['DB_USER']    ?? 'root';
$pass    = $_ENV['DB_PASS']    ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
        die("Database connection failed: " . $e->getMessage());
    }
    die("Database connection failed. Please try again later.");
}
