<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$appName = $_ENV['APP_NAME'] ?? 'PoyberGallery';
$pageTitle = $pageTitle ?? $appName;
$flash = getFlash();
?>
<!doctype html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?> — <?= e($appName) ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["Inter", "sans-serif"]
                    },
                    colors: {
                        ink: {
                            900: "#0b0f17",
                            800: "#111827",
                            700: "#1f2937",
                            600: "#374151"
                        },
                        accent: {
                            DEFAULT: "#6366f1",
                            hover: "#4f46e5",
                            soft: "#818cf8"
                        },
                    },
                },
            },
        };
    </script>

    <style>
        body {
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(99, 102, 241, 0.18), transparent 60%),
                radial-gradient(900px 500px at 110% 10%, rgba(236, 72, 153, 0.12), transparent 55%),
                #0b0f17;
            min-height: 100vh;
        }
    </style>
</head>

<body class="text-slate-200 font-sans antialiased">

    <header class="max-w-[1400px] mx-auto px-4 sm:px-6 pt-6 pb-4 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 grid place-items-center shadow-lg transition-transform group-hover:scale-105">
                <svg viewBox="0 0 24 24" class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="9" cy="9" r="2" />
                    <path d="M21 15l-5-5L5 21" />
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-semibold tracking-tight"><?= e($appName) ?></h1>
                <p class="text-[11px] text-slate-400 -mt-0.5">Share. Discover. Inspire.</p>
            </div>
        </a>

        <nav class="flex items-center gap-2 text-sm">
            <?php if (isLoggedIn()): ?>
                <a href="upload.php" class="px-3 py-2 rounded-xl text-slate-300 hover:bg-white/5 transition">Upload</a>
                <a href="profile.php" class="px-3 py-2 rounded-xl text-slate-300 hover:bg-white/5 transition">
                    @<?= e($_SESSION['username'] ?? '') ?>
                </a>
                <a href="logout.php" class="px-3 py-2 rounded-xl text-slate-400 hover:bg-rose-500/20 hover:text-rose-200 transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="px-3 py-2 rounded-xl text-slate-300 hover:bg-white/5 transition">Login</a>
                <a href="register.php" class="px-4 py-2 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-lg transition">Sign up</a>
            <?php endif; ?>
        </nav>
    </header>

    <?php if ($flash): ?>
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 mb-3">
            <div class="px-4 py-3 rounded-xl text-sm
      <?= $flash['type'] === 'error' ? 'bg-rose-500/10 border border-rose-500/30 text-rose-200' : '' ?>
      <?= $flash['type'] === 'success' ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-200' : '' ?>
      <?= $flash['type'] === 'info' ? 'bg-indigo-500/10 border border-indigo-500/30 text-indigo-200' : '' ?>
    ">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="max-w-[1400px] mx-auto px-4 sm:px-6 pb-12">