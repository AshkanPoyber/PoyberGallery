<?php

declare(strict_types=1);

require_once __DIR__ . '/theme.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$appName   = $_ENV['APP_NAME'] ?? 'PoyberGallery';
$pageTitle = $pageTitle ?? $appName;
$flash     = getFlash();
$theme     = getTheme();
$isDark    = $theme === 'dark';
?>
<!doctype html>
<html lang="en" class="<?= $isDark ? 'dark' : '' ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle) ?> — <?= e($appName) ?></title>

    <script>
        // Apply theme before render to prevent FOUC
        (() => {
            try {
                const cookie = document.cookie.match(/poybergallery_theme=(dark|light)/);
                const saved = cookie ? cookie[1] : null;
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = saved || (prefersDark ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', theme === 'dark');
            } catch {}
        })();
    </script>

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
                    boxShadow: {
                        glow: "0 0 0 1px rgba(99,102,241,.35), 0 8px 24px -6px rgba(99,102,241,.45)",
                    },
                },
            },
        };
    </script>

    <style>
        html,
        body {
            transition: background-color .3s ease, color .3s ease;
        }

        html.dark body {
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(99, 102, 241, 0.18), transparent 60%),
                radial-gradient(900px 500px at 110% 10%, rgba(236, 72, 153, 0.12), transparent 55%),
                #0b0f17;
            min-height: 100vh;
            color: #e2e8f0;
        }

        html:not(.dark) body {
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(99, 102, 241, 0.10), transparent 60%),
                radial-gradient(900px 500px at 110% 10%, rgba(236, 72, 153, 0.07), transparent 55%),
                #f8fafc;
            min-height: 100vh;
            color: #1e293b;
        }

        #themeToggle svg {
            transition: transform .4s ease, opacity .2s ease;
        }

        html.dark #themeIconSun {
            opacity: 1;
            transform: rotate(0deg) scale(1);
        }

        html.dark #themeIconMoon {
            opacity: 0;
            transform: rotate(-90deg) scale(.6);
        }

        html:not(.dark) #themeIconSun {
            opacity: 0;
            transform: rotate(90deg) scale(.6);
        }

        html:not(.dark) #themeIconMoon {
            opacity: 1;
            transform: rotate(0deg) scale(1);
        }
    </style>
</head>

<body class="font-sans antialiased">

    <header class="max-w-[1400px] mx-auto px-4 sm:px-6 pt-6 pb-4 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 grid place-items-center shadow-glow transition-transform group-hover:scale-105">
                <svg viewBox="0 0 24 24" class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="9" cy="9" r="2" />
                    <path d="M21 15l-5-5L5 21" />
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-semibold tracking-tight"><?= e($appName) ?></h1>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-0.5">Share. Discover. Inspire.</p>
            </div>
        </a>

        <nav class="flex items-center gap-2 text-sm">
            <!-- Search -->
            <a href="search.php"
                title="Search"
                class="w-9 h-9 rounded-xl grid place-items-center text-slate-500 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 transition">
                <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M21 21l-4.3-4.3" />
                </svg>
            </a>
            <!-- Theme toggle -->
            <button
                id="themeToggle"
                type="button"
                title="Toggle theme"
                class="relative w-9 h-9 rounded-xl grid place-items-center text-slate-500 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5 transition">
                <svg id="themeIconSun" viewBox="0 0 24 24" class="absolute w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                </svg>
                <svg id="themeIconMoon" viewBox="0 0 24 24" class="absolute w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>
            </button>

            <?php if (isLoggedIn()): ?>
                <a href="upload.php" class="px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5 transition">Upload</a>
                <a href="profile.php?u=<?= e($_SESSION['username'] ?? '') ?>" class="px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    @<?= e($_SESSION['username'] ?? '') ?>
                </a>
                <a href="settings.php" class="px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5 transition">
                    Settings
                </a>
                <a href="logout.php" class="px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-rose-500/20 hover:text-rose-600 dark:hover:text-rose-200 transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/5 transition">Login</a>
                <a href="register.php" class="px-4 py-2 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-glow transition">Sign up</a>
            <?php endif; ?>
        </nav>
    </header>

    <?php if ($flash): ?>
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 mb-3">
            <div class="px-4 py-3 rounded-xl text-sm border
      <?= $flash['type'] === 'error' ? 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-200' : '' ?>
      <?= $flash['type'] === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-200' : '' ?>
      <?= $flash['type'] === 'info' ? 'bg-indigo-500/10 border-indigo-500/30 text-indigo-700 dark:text-indigo-200' : '' ?>
    ">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="max-w-[1400px] mx-auto px-4 sm:px-6 pb-12">