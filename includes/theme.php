<?php

/**
 * PoyberGallery — Theme Handler (Dark / Light)
 */

declare(strict_types=1);

const THEME_COOKIE = 'poybergallery_theme';
const THEME_COOKIE_DAYS = 365;

/**
 * Get the current theme ('dark' or 'light')
 */
function getTheme(): string
{
    // 1) From cookie
    if (!empty($_COOKIE[THEME_COOKIE])) {
        $t = $_COOKIE[THEME_COOKIE];
        if ($t === 'dark' || $t === 'light') return $t;
    }

    // 2) Default: dark (matches PoyberPaint)
    return 'dark';
}

/**
 * Set theme cookie
 */
function setTheme(string $theme): void
{
    if (!in_array($theme, ['dark', 'light'], true)) {
        $theme = 'dark';
    }

    setcookie(THEME_COOKIE, $theme, [
        'expires'  => time() + (THEME_COOKIE_DAYS * 86400),
        'path'     => '/',
        'samesite' => 'Lax',
    ]);

    $_COOKIE[THEME_COOKIE] = $theme; // for current request
}

/**
 * Returns 'dark' class string if theme is dark
 */
function themeClass(): string
{
    return getTheme() === 'dark' ? 'dark' : '';
}
