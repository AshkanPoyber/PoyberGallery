<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

session_start();
if (isLoggedIn()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $result = loginUser(
            $pdo,
            $_POST['identifier'] ?? '',
            $_POST['password'] ?? ''
        );

        if ($result['success']) {
            setFlash('success', 'Welcome back, ' . $result['user']['username'] . '! 👋');
            redirect('index.php');
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto mt-8">
    <div class="rounded-2xl border border-white/5 bg-ink-800/70 backdrop-blur p-6 shadow-xl">
        <h2 class="text-xl font-semibold mb-1">Welcome back</h2>
        <p class="text-sm text-slate-400 mb-6">Log in to your PoyberGallery account.</p>

        <?php if ($error): ?>
            <div class="mb-4 px-3 py-2 rounded-lg text-sm bg-rose-500/10 border border-rose-500/30 text-rose-200">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Username or Email</label>
                <input type="text" name="identifier" required autofocus
                    value="<?= e($_POST['identifier'] ?? '') ?>"
                    class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition" />
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition" />
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-lg transition">
                Log in
            </button>
        </form>

        <p class="text-center text-sm text-slate-400 mt-6">
            Don't have an account? <a href="register.php" class="text-accent-soft hover:text-white transition">Sign up</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>