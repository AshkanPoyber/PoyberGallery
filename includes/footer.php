</main>

<footer class="max-w-[1400px] mx-auto px-4 sm:px-6 pb-6 text-center text-xs text-slate-500 dark:text-slate-400">
    <p>
        © <?= date('Y') ?>
        <span class="text-slate-600 dark:text-slate-400"><?= e($_ENV['APP_NAME'] ?? 'PoyberGallery') ?></span>
        · Built by
        <a href="https://github.com/ashkanpoyber" target="_blank" class="text-accent-soft hover:text-slate-900 dark:hover:text-white transition">AshkanPoyber</a>
    </p>
</footer>

<script>
    // Theme toggle (AJAX)
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', async () => {
            const isDark = document.documentElement.classList.contains('dark');
            const next = isDark ? 'light' : 'dark';

            // Instant visual feedback
            document.documentElement.classList.toggle('dark', next === 'dark');

            // Persist server-side
            try {
                await fetch('toggle-theme.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                });
            } catch (err) {
                // Rollback on failure
                document.documentElement.classList.toggle('dark', isDark);
                console.warn('Theme save failed', err);
            }
        });
    }
</script>

</body>

</html>