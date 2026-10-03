<?php
/**
 * PoyberGallery — Add comment endpoint
 */

declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

session_start();
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'login_required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$imageId = (int)($input['image_id'] ?? 0);
$text    = trim((string)($input['text'] ?? ''));
$csrf    = $input['csrf_token'] ?? '';

if ($imageId <= 0 || $text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

if (mb_strlen($text) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'too_long']);
    exit;
}

if (!verifyCsrf($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'invalid_csrf']);
    exit;
}

// Does image exist?
$stmt = $pdo->prepare("SELECT id FROM images WHERE id = ? LIMIT 1");
$stmt->execute([$imageId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'image_not_found']);
    exit;
}

// Insert comment
$stmt = $pdo->prepare(
    "INSERT INTO comments (user_id, image_id, text) VALUES (?, ?, ?)"
);
$stmt->execute([currentUserId(), $imageId, $text]);
$commentId = (int)$pdo->lastInsertId();

// Fetch the inserted comment with username
$stmt = $pdo->prepare(
    "SELECT c.id, c.text, c.created_at, u.username, u.id AS user_id
     FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.id = ? LIMIT 1"
);
$stmt->execute([$commentId]);
$comment = $stmt->fetch();

echo json_encode([
    'success' => true,
    'comment' => [
        'id'         => (int)$comment['id'],
        'text'       => $comment['text'],
        'username'   => $comment['username'],
        'user_id'    => (int)$comment['user_id'],
        'created_at' => $comment['created_at'],
        'is_mine'    => true,
    ],
]);