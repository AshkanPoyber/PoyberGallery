<?php

/**
 * PoyberGallery — Delete comment endpoint
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

$input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$commentId = (int)($input['comment_id'] ?? 0);
$csrf      = $input['csrf_token'] ?? '';

if ($commentId <= 0 || !verifyCsrf($csrf)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

// Fetch comment + image owner
$stmt = $pdo->prepare(
    "SELECT c.id, c.user_id, i.user_id AS image_owner_id
     FROM comments c
     JOIN images i ON i.id = c.image_id
     WHERE c.id = ? LIMIT 1"
);
$stmt->execute([$commentId]);
$c = $stmt->fetch();

if (!$c) {
    http_response_code(404);
    echo json_encode(['error' => 'not_found']);
    exit;
}

$isCommentOwner = (int)$c['user_id'] === currentUserId();
$isImageOwner   = (int)$c['image_owner_id'] === currentUserId();

if (!$isCommentOwner && !$isImageOwner) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$commentId]);

echo json_encode(['success' => true]);
