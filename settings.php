<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

session_start();
if (!isLoggedIn()) redirect('login.php');

$userId = currentUserId();
$error  = '';
$success = '';

// Fetch current user
$stmt = $pdo->prepare("SELECT username, email, bio FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'profile') {
            $bio = trim((string)($_POST['bio'] ?? ''));
            if (mb_strlen($bio) > 300) {
                $error = 'Bio must be under 300 characters.';
            } else {
                $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?")
                    ->execute([$bio ?: null, $userId]);
                $success = 'Profile updated.';
                $user['bio'] = $bio;
            }
        } elseif ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($new) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($new !== $confirm) {
                $error = 'New passwords do not match.';
            } else {
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
                $hash = $stmt->fetchColumn();

                if (!password_verify($current, $hash)) {
                    $error = 'Current password is incorrect.';
                } else {
                    $newHash = password_hash($new, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                        ->execute([$newHash, $userId]);
                    $success = 'Password changed.';
                }
            }
        }
    }
}

$pageTitle = 'Settings';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto mt-6 space-y-6">

  <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Settings</h1>

  <?php if ($error): ?>
    <div class="px-4 py-3 rounded-xl text-sm bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-200">
      <?= e($error) ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="px-4 py-3 rounded-xl text-sm bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-200">
      <?= e($success) ?>
    </div>
  <?php endif; ?>

  <!-- Profile -->
  <div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 backdrop-blur p-6">
    <h2 class="text-lg font-semibold mb-4 text-slate-900 dark:text-slate-100">Profile</h2>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="profile" />

      <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1.5">Username</label>
        <input type="text" value="<?= e($user['username']) ?>" disabled
               class="w-full px-3 py-2.5 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-slate-500 cursor-not-allowed" />
        <p class="text-[11px] text-slate-500 mt-1.5">Username cannot be changed.</p>
      </div>

      <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1.5">Email</label>
        <input type="email" value="<?= e($user['email']) ?>" disabled
               class="w-full px-3 py-2.5 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-slate-500 cursor-not-allowed" />
      </div>

      <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1.5">Bio</label>
        <textarea name="bio" rows="3" maxlength="300"
                  placeholder="Tell others about yourself…"
                  class="w-full px-3 py-2.5 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition resize-none text-slate-900 dark:text-slate-100 placeholder-slate-400"><?= e($user['bio'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="px-5 py-2.5 rounded-xl text-sm bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-glow transition">
        Save profile
      </button>
    </form>
  </div>

  <!-- Password -->
  <div class="rounded-2xl border border-black/5 dark:border-white/5 bg-white/70 dark:bg-ink-800/70 backdrop-blur p-6">
    <h2 class="text-lg font-semibold mb-4 text-slate-900 dark:text-slate-100">Change password</h2>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="password" />

      <div>
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1.5">Current password</label>
        <input type="password" name="current_password" required
               class="w-full px-3 py-2.5 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition text-slate-900 dark:text-slate-100" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1.5">New password</label>
          <input type="password" name="new_password" required minlength="8"
                 class="w-full px-3 py-2.5 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition text-slate-900 dark:text-slate-100" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1.5">Confirm</label>
          <input type="password" name="confirm_password" required minlength="8"
                 class="w-full px-3 py-2.5 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition text-slate-900 dark:text-slate-100" />
        </div>
      </div>

      <button type="submit" class="px-5 py-2.5 rounded-xl text-sm bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-glow transition">
        Change password
      </button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>