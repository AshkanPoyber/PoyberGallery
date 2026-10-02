<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();
if (!isLoggedIn()) redirect('login.php');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('index.php');

// Fetch image to verify ownership
$stmt = $pdo->prepare("SELECT user_id, file_path FROM images WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$img = $stmt->fetch();

if (!$img) {
    setFlash('error', 'Image not found.');
    redirect('index.php');
}

if ((int)$img['user_id'] !== currentUserId()) {
    setFlash('error', "You can't delete someone else's image.");
    redirect('image.php?id=' . $id);
}

// Delete file
$file = __DIR__ . '/uploads/' . $img['file_path'];
if (file_exists($file)) {
    @unlink($file);
}

// Delete DB row (cascade will remove likes/comments)
$pdo->prepare("DELETE FROM images WHERE id = ?")->execute([$id]);

setFlash('success', 'Image deleted.');
redirect('index.php');
