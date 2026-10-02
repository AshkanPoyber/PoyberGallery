<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<div class="text-center py-20">
    <h2 class="text-3xl font-semibold mb-3">Welcome to PoyberGallery 🖼️</h2>
    <p class="text-slate-400">
        <?php if (isLoggedIn()): ?>
            Hey <strong class="text-accent-soft"><?= e($_SESSION['username']) ?></strong>! The gallery will be built here.
        <?php else: ?>
            <a href="register.php" class="text-accent-soft hover:text-white">Sign up</a> to start sharing your art.
        <?php endif; ?>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>