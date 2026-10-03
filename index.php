<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

// Filter mode: 'all' or 'following'
$mode = $_GET['mode'] ?? 'all';
if ($mode !== 'following') $mode = 'all';

$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$params = [];
$where  = '';

if ($mode === 'following' && isLoggedIn()) {
    $where = "WHERE i.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)";
    $params[] = currentUserId();
}

// Total count
$countSql = "SELECT COUNT(*) FROM images i $where";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

// Fetch
$sql = "SELECT i.id, i.title, i.file_path, i.created_at, i.views,
               u.username,
               (SELECT COUNT(*) FROM likes WHERE image_id = i.id) AS likes
        FROM images i
        JOIN users u ON u.id = i.user_id
        $where
        ORDER BY i.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
foreach ($params as $i => $p) {
    $stmt->bindValue($i + 1, $p);
}
$stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll();

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
            <?= $mode === 'following' ? 'Following feed' : 'Latest uploads' ?>
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            <?= number_format($total) ?> image<?= $total === 1 ? '' : 's' ?>
            <?= $mode === 'following' ? 'from people you follow' : 'shared' ?>
        </p>
    </div>

    <div class="flex items-center gap-2">
        <?php if (isLoggedIn()): ?>
            <!-- Mode switch -->
            <div class="flex rounded-xl bg-black/5 dark:bg-white/5 p-1 text-xs">
                <a href="?mode=all"
                    class="px-3 py-1.5 rounded-lg transition <?= $mode === 'all' ? 'bg-white dark:bg-ink-700 text-slate-900 dark:text-slate-100 font-medium shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                    All
                </a>
                <a href="?mode=following"
                    class="px-3 py-1.5 rounded-lg transition <?= $mode === 'following' ? 'bg-white dark:bg-ink-700 text-slate-900 dark:text-slate-100 font-medium shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
                    Following
                </a>
            </div>

            <a href="upload.php"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-glow transition text-sm">
                + Upload
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($images)): ?>
    <div class="text-center py-20 rounded-2xl border border-dashed border-black/10 dark:border-white/10">
        <svg viewBox="0 0 24 24" class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <circle cx="9" cy="9" r="2" />
            <path d="M21 15l-5-5L5 21" />
        </svg>
        <p class="text-slate-500 dark:text-slate-400">
            <?= $mode === 'following'
                ? "No images from people you follow yet. Follow some creators to see their work here."
                : "No images yet. Be the first to share!" ?>
        </p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="inline-block mt-3 text-accent-soft hover:text-slate-900 dark:hover:text-white transition">
                Sign up to upload →
            </a>
        <?php endif; ?>
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
                    <div class="flex items-center gap-3 text-[11px] text-slate-300 mt-1">
                        <span>@<?= e($img['username']) ?></span>
                        <span class="flex items-center gap-1">
                            <svg viewBox="0 0 24 24" class="w-3 h-3" fill="currentColor">
                                <path d="M12 21s-7-4.5-9.5-9C.5 8 2 4 6 4c2 0 3.5 1 4.5 2.5C11.5 5 13 4 15 4c4 0 5.5 4 3.5 8C19 16.5 12 21 12 21z" />
                            </svg>
                            <?= (int)$img['likes'] ?>
                        </span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-8">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(['mode' => $mode, 'page' => $page - 1]) ?>"
                    class="px-4 py-2 rounded-xl bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-sm transition">← Prev</a>
            <?php endif; ?>
            <span class="text-sm text-slate-500 dark:text-slate-400 px-3">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(['mode' => $mode, 'page' => $page + 1]) ?>"
                    class="px-4 py-2 rounded-xl bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 text-sm transition">Next →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>