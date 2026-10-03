<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$q = trim((string)($_GET['q'] ?? ''));
$tab = $_GET['tab'] ?? 'images'; // 'images' or 'users'

$images = [];
$users  = [];

if ($q !== '') {
    if ($tab === 'images') {
        $stmt = $pdo->prepare(
            "SELECT i.id, i.title, i.description, i.file_path, i.created_at,
                    u.username,
                    (SELECT COUNT(*) FROM likes WHERE image_id = i.id) AS likes
             FROM images i
             JOIN users u ON u.id = i.user_id
             WHERE i.title LIKE ? OR i.description LIKE ?
             ORDER BY i.created_at DESC
             LIMIT 40"
        );
        $like = '%' . $q . '%';
        $stmt->execute([$like, $like]);
        $images = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare(
            "SELECT u.id, u.username, u.bio, u.created_at,
                    (SELECT COUNT(*) FROM images WHERE user_id = u.id) AS images_count
             FROM users u
             WHERE u.username LIKE ?
             ORDER BY u.username ASC
             LIMIT 40"
        );
        $stmt->execute(['%' . $q . '%']);
        $users = $stmt->fetchAll();
    }
}

$pageTitle = $q !== '' ? 'Search: ' . $q : 'Search';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto mt-4">

  <!-- Search form -->
  <form method="GET" class="mb-6">
    <div class="relative">
      <svg viewBox="0 0 24 24" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
      </svg>
      <input type="text" name="q" value="<?= e($q) ?>" autofocus
             placeholder="Search images and users…"
             class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition text-slate-900 dark:text-slate-100 placeholder-slate-400" />
    </div>
    <input type="hidden" name="tab" value="<?= e($tab) ?>" />
  </form>

  <?php if ($q === ''): ?>
    <!-- Empty state: no query -->
    <div class="text-center py-16">
      <svg viewBox="0 0 24 24" class="w-12 h-12 mx-auto text-slate-600 dark:text-slate-500 mb-3" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
      </svg>
      <p class="text-slate-500 dark:text-slate-400">Start typing to search for images and users.</p>
    </div>
  <?php else: ?>

    <!-- Tabs -->
    <div class="flex items-center gap-2 mb-5 border-b border-black/5 dark:border-white/5">
      <a href="?q=<?= urlencode($q) ?>&tab=images"
         class="px-4 py-2 text-sm font-medium border-b-2 transition -mb-px <?= $tab === 'images' ? 'border-accent text-accent-soft' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
        Images <span class="text-xs opacity-70">(<?= count($images) ?>)</span>
      </a>
      <a href="?q=<?= urlencode($q) ?>&tab=users"
         class="px-4 py-2 text-sm font-medium border-b-2 transition -mb-px <?= $tab === 'users' ? 'border-accent text-accent-soft' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' ?>">
        Users <span class="text-xs opacity-70">(<?= count($users) ?>)</span>
      </a>
    </div>

    <!-- Results -->
    <?php if ($tab === 'images'): ?>

      <?php if (empty($images)): ?>
        <div class="text-center py-16 rounded-2xl border border-dashed border-black/10 dark:border-white/10">
          <p class="text-slate-500 dark:text-slate-400">No images found for "<strong><?= e($q) ?></strong>".</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
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
                <p class="text-[11px] text-slate-300 mt-1">@<?= e($img['username']) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>

      <?php if (empty($users)): ?>
        <div class="text-center py-16 rounded-2xl border border-dashed border-black/10 dark:border-white/10">
          <p class="text-slate-500 dark:text-slate-400">No users found for "<strong><?= e($q) ?></strong>".</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($users as $u): ?>
            <a href="profile.php?u=<?= e($u['username']) ?>"
               class="flex items-center gap-4 p-4 rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 hover:border-accent/40 transition">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 grid place-items-center text-white text-lg font-semibold flex-shrink-0">
                <?= e(strtoupper(substr($u['username'], 0, 1))) ?>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-slate-900 dark:text-slate-100">@<?= e($u['username']) ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                  <?= e($u['bio'] ?: 'No bio yet.') ?>
                </p>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="text-sm font-medium text-slate-900 dark:text-slate-100"><?= (int)$u['images_count'] ?></p>
                <p class="text-[11px] text-slate-500">images</p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>