<?php

/**
 * PoyberGallery — Follow / Unfollow endpoint
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

$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$userId = (int)($input['user_id'] ?? 0);
$csrf   = $input['csrf_token'] ?? '';

if ($userId <= 0 || !verifyCsrf($csrf)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

$me = currentUserId();

if ($userId === $me) {
    http_response_code(400);
    echo json_encode(['error' => 'cannot_follow_self']);
    exit;
}

// User exists?
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'user_not_found']);
    exit;
}

// Already following?
$stmt = $pdo->prepare(
    "SELECT id FROM follows WHERE follower_id = ? AND following_id = ? LIMIT 1"
);
$stmt->execute([$me, $userId]);
$existing = $stmt->fetch();

if ($existing) {
    $pdo->prepare("DELETE FROM follows WHERE id = ?")->execute([$existing['id']]);
    $following = false;
} else {
    $pdo->prepare(
        "INSERT INTO follows (follower_id, following_id) VALUES (?, ?)"
    )->execute([$me, $userId]);
    $following = true;
}

// New followers count for that user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
$stmt->execute([$userId]);
$followers = (int)$stmt->fetchColumn();

echo json_encode([
    'success'   => true,
    'following' => $following,
    'followers' => $followers,
]);
