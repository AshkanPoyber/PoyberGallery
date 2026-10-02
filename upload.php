<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

session_start();
if (!isLoggedIn()) {
    setFlash('info', 'Please log in to upload images.');
    redirect('login.php');
}

$error = '';
$old   = ['title' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $old         = ['title' => $title, 'description' => $description];

        if ($title === '' || mb_strlen($title) > 120) {
            $error = 'Title is required and must be under 120 characters.';
        } elseif (empty($_FILES['image'])) {
            $error = 'Please choose an image to upload.';
        } else {
            $up = uploadImage($_FILES['image']);

            if (!$up['success']) {
                $error = $up['error'];
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO images (user_id, title, description, file_path)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([
                    currentUserId(),
                    $title,
                    $description ?: null,
                    $up['filename'],
                ]);

                setFlash('success', 'Your image was uploaded! 🎉');
                redirect('image.php?id=' . $pdo->lastInsertId());
            }
        }
    }
}

$pageTitle = 'Upload';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto mt-6">
    <div class="rounded-2xl border border-white/5 bg-ink-800/70 backdrop-blur p-6 shadow-xl">
        <h2 class="text-xl font-semibold mb-1">Upload an image</h2>
        <p class="text-sm text-slate-400 mb-6">Share something you're proud of.</p>

        <?php if ($error): ?>
            <div class="mb-4 px-3 py-2 rounded-lg text-sm bg-rose-500/10 border border-rose-500/30 text-rose-200">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />

            <!-- Dropzone -->
            <div id="dropzone"
                class="relative border-2 border-dashed border-white/15 hover:border-accent/50 rounded-2xl p-8 text-center transition cursor-pointer">
                <input type="file" name="image" id="imageInput" accept="image/*" required class="sr-only" />
                <div id="dropContent" class="space-y-2">
                    <svg viewBox="0 0 24 24" class="w-10 h-10 mx-auto text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <path d="M17 8l-5-5-5 5" />
                        <path d="M12 3v12" />
                    </svg>
                    <p class="text-sm text-slate-300"><strong class="text-accent-soft">Click to browse</strong> or drag & drop</p>
                    <p class="text-xs text-slate-500">JPG, PNG, GIF, or WebP · Max 5MB</p>
                </div>
                <img id="preview" class="hidden mx-auto max-h-64 rounded-xl" alt="Preview" />
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Title</label>
                <input type="text" name="title" required maxlength="120"
                    value="<?= e($old['title']) ?>"
                    placeholder="e.g. Sunset over the mountains"
                    class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition" />
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Description <span class="text-slate-600">(optional)</span></label>
                <textarea name="description" rows="3" maxlength="2000"
                    placeholder="Tell us about it…"
                    class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 transition resize-none"><?= e($old['description']) ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl bg-gradient-to-br from-accent to-fuchsia-500 text-white font-medium hover:shadow-lg transition">
                    Upload image
                </button>
                <a href="index.php"
                    class="px-4 py-2.5 rounded-xl text-slate-300 bg-white/5 hover:bg-white/10 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const dz = document.getElementById('dropzone');
    const input = document.getElementById('imageInput');
    const preview = document.getElementById('preview');
    const dropContent = document.getElementById('dropContent');

    dz.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
        const f = input.files[0];
        if (!f) return;
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            dropContent.classList.add('hidden');
        };
        reader.readAsDataURL(f);
    });

    // Drag & drop
    ['dragenter', 'dragover'].forEach(ev => {
        dz.addEventListener(ev, e => {
            e.preventDefault();
            dz.classList.add('border-accent', 'bg-accent/5');
        });
    });
    ['dragleave', 'drop'].forEach(ev => {
        dz.addEventListener(ev, e => {
            e.preventDefault();
            dz.classList.remove('border-accent', 'bg-accent/5');
        });
    });
    dz.addEventListener('drop', e => {
        const f = e.dataTransfer.files[0];
        if (f) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>