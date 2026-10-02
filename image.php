<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('index.php');

// Fetch image
$stmt = $pdo->prepare(
    "SELECT i.*, u.username, u.id AS owner_id,
            (SELECT COUNT(*) FROM likes WHERE image_id = i.id) AS likes
     FROM images i
     JOIN users u ON u.id = i.user_id
     WHERE i.id = ? LIMIT 1"
);
$stmt->execute([$id]);
$img = $stmt->fetch();

if (!$img) {
    setFlash('error', 'Image not found.');
    redirect('index.php');
}

// Increment views
$pdo->prepare("UPDATE images SET views = views + 1 WHERE id = ?")->execute([$id]);

$pageTitle = $img['title'];
require __DIR__ . '/includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6">
    <!-- Image -->
    <div class="rounded-2xl border border-white/5 bg-ink-800/50 overflow-hidden">
        <img src="uploads/<?= e($img['file_path']) ?>"
            alt="<?= e($img['title']) ?>"
            class="w-full h-auto" />
    </div>

    <!-- Sidebar -->
    <aside class="space-y-4">
        <div class="rounded-2xl border border-white/5 bg-ink-800/70 backdrop-blur p-5">
            <h1 class="text-xl font-semibold mb-3"><?= e($img['title']) ?></h1>

            <div class="flex items-center gap-2 text-sm text-slate-400 mb-4">
                <a href="profile.php?u=<?= e($img['username']) ?>" class="text-accent-soft hover:text-white transition">
                    @<?= e($img['username']) ?>
                </a>
                <span>·</span>
                <time><?= date('M j, Y', strtotime($img['created_at'])) ?></time>
            </div>

            <?php if (!empty($img['description'])): ?>
                <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap"><?= e($img['description']) ?></p>
            <?php endif; ?>

            <div class="flex items-center gap-4 mt-5 pt-4 border-t border-white/5 text-xs text-slate-500">
                <span><?= number_format((int)$img['views']) ?> views</span>
                <span><?= (int)$img['likes'] ?> likes</span>
            </div>
        </div>

        <?php if (isLoggedIn() && currentUserId() === (int)$img['owner_id']): ?>
            <div class="rounded-2xl border border-white/5 bg-ink-800/70 backdrop-blur p-4">
                <p class="text-xs text-slate-400 mb-3">You own this image</p>
                <a href="delete.php?id=<?= (int)$img['id'] ?>"
                    onclick="return confirm('Delete this image permanently?')"
                    class="block text-center py-2 rounded-xl text-sm text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 transition">
                    Delete image
                </a>
            </div>
        <?php endif; ?>
    </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>