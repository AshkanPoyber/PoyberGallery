<?php

/**
 * PoyberGallery — Utility Functions
 */

declare(strict_types=1);

/**
 * Escape output for HTML (prevent XSS)
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirect to a URL and exit
 */
function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged-in user's ID
 */
function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Generate CSRF token
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function verifyCsrf(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message — set
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message — get & clear
 */
function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Handle image upload
 * @return array{success: bool, error?: string, filename?: string}
 */
function uploadImage(array $file): array
{
    // ---- Check upload errors ----
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file upload.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'File is too large (server limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File is too large.',
            UPLOAD_ERR_PARTIAL    => 'Upload was interrupted.',
            UPLOAD_ERR_NO_FILE    => 'Please choose a file.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: no temp folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: cannot write file.',
            UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
        ];
        return ['success' => false, 'error' => $messages[$file['error']] ?? 'Upload failed.'];
    }

    // ---- Size check ----
    $maxSize = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5 * 1024 * 1024);
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File is larger than ' . round($maxSize / 1024 / 1024, 1) . ' MB.'];
    }

    // ---- MIME check ----
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed, true)) {
        return ['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP are allowed.'];
    }

    // ---- Generate safe filename ----
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $ext      = $extMap[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;

    // ---- Make sure uploads dir exists ----
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;

    // ---- Move file ----
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Could not save the file.'];
    }

    return ['success' => true, 'filename' => $filename];
}
