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
            (SELECT COUNT(*) FROM likes WHERE image_id = i.id) AS likes_count
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

// Did current user like it?
$userLiked = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ? LIMIT 1");
    $stmt->execute([currentUserId(), $id]);
    $userLiked = (bool)$stmt->fetch();
}

// Fetch comments
$stmt = $pdo->prepare(
    "SELECT c.id, c.text, c.created_at, c.user_id, u.username
     FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.image_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

$pageTitle = $img['title'];
require __DIR__ . '/includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-6">

    <!-- Image -->
    <div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/60 dark:bg-ink-800/50 overflow-hidden">
        <img src="uploads/<?= e($img['file_path']) ?>"
            alt="<?= e($img['title']) ?>"
            class="w-full h-auto" />
    </div>

    <!-- Sidebar -->
    <aside class="space-y-4">

        <!-- Info card -->
        <div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 backdrop-blur p-5">
            <h1 class="text-xl font-semibold mb-3 text-slate-900 dark:text-slate-100"><?= e($img['title']) ?></h1>

            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-4">
                <a href="profile.php?u=<?= e($img['username']) ?>" class="text-accent-soft hover:text-slate-900 dark:hover:text-white transition">
                    @<?= e($img['username']) ?>
                </a>
                <span>·</span>
                <time><?= date('M j, Y', strtotime($img['created_at'])) ?></time>
            </div>

            <?php if (!empty($img['description'])): ?>
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap mb-4"><?= e($img['description']) ?></p>
            <?php endif; ?>

            <div class="flex items-center gap-4 pt-4 border-t border-black/5 dark:border-white/5">
                <!-- Like button -->
                <button id="likeBtn"
                    data-image-id="<?= (int)$img['id'] ?>"
                    data-liked="<?= $userLiked ? '1' : '0' ?>"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm transition <?= $userLiked ? 'bg-rose-500/15 text-rose-500' : 'bg-black/5 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-rose-500/10 hover:text-rose-500' ?>">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="<?= $userLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21s-7-4.5-9.5-9C.5 8 2 4 6 4c2 0 3.5 1 4.5 2.5C11.5 5 13 4 15 4c4 0 5.5 4 3.5 8C19 16.5 12 21 12 21z" />
                    </svg>
                    <span id="likeCount"><?= (int)$img['likes_count'] ?></span>
                </button>

                <span class="text-xs text-slate-500"><?= number_format((int)$img['views']) ?> views</span>
            </div>
        </div>

        <!-- Owner actions -->
        <?php if (isLoggedIn() && currentUserId() === (int)$img['owner_id']): ?>
            <div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 backdrop-blur p-4">
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">You own this image</p>
                <a href="delete.php?id=<?= (int)$img['id'] ?>"
                    onclick="return confirm('Delete this image permanently?')"
                    class="block text-center py-2 rounded-xl text-sm text-rose-500 bg-rose-500/10 hover:bg-rose-500/20 transition">
                    Delete image
                </a>
            </div>
        <?php endif; ?>

        <!-- Comments -->
        <div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 backdrop-blur p-5">
            <h2 class="text-sm font-semibold mb-3 text-slate-900 dark:text-slate-100">
                Comments <span id="commentCount" class="text-slate-500 font-normal">(<?= count($comments) ?>)</span>
            </h2>

            <?php if (isLoggedIn()): ?>
                <form id="commentForm" class="mb-4" data-image-id="<?= (int)$img['id'] ?>">
                    <textarea id="commentText" rows="2" maxlength="1000" required
                        placeholder="Write a comment…"
                        class="w-full px-3 py-2 rounded-xl text-sm bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition resize-none text-slate-900 dark:text-slate-100 placeholder-slate-400"></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-medium bg-gradient-to-br from-accent to-fuchsia-500 text-white hover:shadow-glow transition">
                            Post
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    <a href="login.php" class="text-accent-soft hover:text-slate-900 dark:hover:text-white transition">Log in</a> to leave a comment.
                </p>
            <?php endif; ?>

            <ul id="commentList" class="space-y-3">
                <?php foreach ($comments as $c): ?>
                    <?php $isMine = isLoggedIn() && currentUserId() === (int)$c['user_id']; ?>
                    <li class="flex gap-3 text-sm" data-comment-id="<?= (int)$c['id'] ?>">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-accent to-fuchsia-500 grid place-items-center text-white text-xs font-semibold flex-shrink-0">
                            <?= e(strtoupper(substr($c['username'], 0, 1))) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2">
                                <a href="profile.php?u=<?= e($c['username']) ?>" class="font-medium text-slate-900 dark:text-slate-100 hover:text-accent-soft transition">
                                    @<?= e($c['username']) ?>
                                </a>
                                <time class="text-[11px] text-slate-500"><?= date('M j', strtotime($c['created_at'])) ?></time>
                                <?php if ($isMine || (isLoggedIn() && currentUserId() === (int)$img['owner_id'])): ?>
                                    <button class="comment-delete ml-auto text-[11px] text-slate-400 hover:text-rose-500 transition"
                                        data-comment-id="<?= (int)$c['id'] ?>">
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="text-slate-700 dark:text-slate-300 mt-0.5 whitespace-pre-wrap break-words"><?= e($c['text']) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p id="commentEmpty" class="<?= empty($comments) ? '' : 'hidden' ?> text-sm text-slate-500 dark:text-slate-400 mt-2">
                No comments yet. Be the first!
            </p>
        </div>
    </aside>
</div>

<!-- CSRF token for JS -->
<meta name="csrf-token" content="<?= e(csrfToken()) ?>" />

<?php require __DIR__ . '/includes/footer.php'; ?>