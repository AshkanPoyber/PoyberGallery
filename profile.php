<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$username = trim((string)($_GET['u'] ?? ''));
if ($username === '') redirect('index.php');

// Fetch user
$stmt = $pdo->prepare("SELECT id, username, email, bio, created_at FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'User not found.');
    redirect('index.php');
}

$userId = (int)$user['id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM images WHERE user_id = ?");
$stmt->execute([$userId]);
$imageCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ?");
$stmt->execute([$userId]);
$likesGiven = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM likes l
     JOIN images i ON i.id = l.image_id
     WHERE i.user_id = ?"
);
$stmt->execute([$userId]);
$likesReceived = (int)$stmt->fetchColumn();

// User's images
$stmt = $pdo->prepare(
    "SELECT id, title, file_path, created_at,
            (SELECT COUNT(*) FROM likes WHERE image_id = images.id) AS likes
     FROM images
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$userId]);
$images = $stmt->fetchAll();

$isOwnProfile = isLoggedIn() && currentUserId() === $userId;
$pageTitle = '@' . $user['username'];

require __DIR__ . '/includes/header.php';
?>

<div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 backdrop-blur p-6 mb-6">
    <div class="flex items-start gap-5">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-accent to-fuchsia-500 grid place-items-center text-white text-3xl font-semibold flex-shrink-0 shadow-glow">
            <?= e(strtoupper(substr($user['username'], 0, 1))) ?>
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">@<?= e($user['username']) ?></h1>
            <?php if (!empty($user['bio'])): ?>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 whitespace-pre-wrap"><?= e($user['bio']) ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-500 italic mt-2">No bio yet.</p>
            <?php endif; ?>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600 dark:text-slate-400 mt-4">
                <span><strong class="text-slate-900 dark:text-slate-100"><?= number_format($imageCount) ?></strong> images</span>
                <span><strong class="text-slate-900 dark:text-slate-100"><?= number_format($likesReceived) ?></strong> likes received</span>
                <span><strong class="text-slate-900 dark:text-slate-100"><?= number_format($likesGiven) ?></strong> likes given</span>
                <span class="text-xs text-slate-500">Joined <?= date('M Y', strtotime($user['created_at'])) ?></span>
            </div>

            <?php if ($isOwnProfile): ?>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="upload.php" class="px-4 py-2 rounded-xl text-sm bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-glow transition">
                        + Upload
                    </a>
                    <a href="settings.php" class="px-4 py-2 rounded-xl text-sm text-slate-700 dark:text-slate-300 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition">
                        Edit profile
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<h2 class="text-lg font-semibold mb-4 text-slate-900 dark:text-slate-100">
    <?= $isOwnProfile ? 'Your images' : 'Images by @' . e($user['username']) ?>
</h2>

<?php if (empty($images)): ?>
    <div class="text-center py-16 rounded-2xl border border-dashed border-black/10 dark:border-white/10">
        <p class="text-slate-500 dark:text-slate-400">
            <?= $isOwnProfile ? "You haven't uploaded anything yet." : "@" . e($user['username']) . " hasn't uploaded anything yet." ?>
        </p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($images as $img): ?>
            <a href="image.php?id=<?= (int)$img['id'] ?>"
                class="group relative aspect-square rounded-2xl overflow-hidden border border-black/5 dark:border-white/5 bg-white/50 dark:bg-ink-800/50 hover:border-accent/40 transition">
                <img src="uploads/<?= e($img['file_path']) ?>"
                    alt="<?= e($img['title']) ?>"
                    loading="lazy"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="absolute inset-x-0 bottom-0 p-3 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all">
                    <p class="text-sm font-medium text-white truncate"><?= e($img['title']) ?></p>
                    <p class="text-[11px] text-slate-300 mt-1 flex items-center gap-1">
                        <svg viewBox="0 0 24 24" class="w-3 h-3" fill="currentColor">
                            <path d="M12 21s-7-4.5-9.5-9C.5 8 2 4 6 4c2 0 3.5 1 4.5 2.5C11.5 5 13 4 15 4c4 0 5.5 4 3.5 8C19 16.5 12 21 12 21z" />
                        </svg>
                        <?= (int)$img['likes'] ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>