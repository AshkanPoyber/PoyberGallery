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
