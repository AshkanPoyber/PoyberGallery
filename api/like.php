<?php
/**
 * PoyberGallery — Like / Unlike endpoint
 */

declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

session_start();
header('Content-Type: application/json');

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'login_required']);
    exit;
}

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

// Read JSON or form data
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$imageId = (int)($input['image_id'] ?? 0);
$csrf    = $input['csrf_token'] ?? '';

if ($imageId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_image']);
    exit;
}

if (!verifyCsrf($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'invalid_csrf']);
    exit;
}

// Does the image exist?
$stmt = $pdo->prepare("SELECT id FROM images WHERE id = ? LIMIT 1");
$stmt->execute([$imageId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'image_not_found']);
    exit;
}

$userId = currentUserId();

// Already liked?
$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ? LIMIT 1");
$stmt->execute([$userId, $imageId]);
$existing = $stmt->fetch();

if ($existing) {
    // Unlike
    $pdo->prepare("DELETE FROM likes WHERE id = ?")->execute([$existing['id']]);
    $liked = false;
} else {
    // Like
    $pdo->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)")
        ->execute([$userId, $imageId]);
    $liked = true;
}

// Get new count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE image_id = ?");
$stmt->execute([$imageId]);
$count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'liked'   => $liked,
    'count'   => $count,
]);