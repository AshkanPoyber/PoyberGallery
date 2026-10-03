<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/theme.php';

$current = getTheme();
$next    = $current === 'dark' ? 'light' : 'dark';
setTheme($next);

// If AJAX request → return JSON
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    header('Content-Type: application/json');
    echo json_encode(['theme' => $next]);
    exit;
}

// Otherwise → redirect back
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $referer");
exit;
