<?php

/**
 * PoyberGallery — Authentication
 */

declare(strict_types=1);

/**
 * Register a new user
 * @return array{success: bool, error?: string, user_id?: int}
 */
function registerUser(PDO $pdo, string $username, string $email, string $password): array
{
    // ---- Validate ----
    $username = trim($username);
    $email    = trim($email);

    if (strlen($username) < 3 || strlen($username) > 30) {
        return ['success' => false, 'error' => 'Username must be between 3 and 30 characters.'];
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['success' => false, 'error' => 'Username can only contain letters, numbers, and underscores.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters.'];
    }

    // ---- Check duplicates ----
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Username or email is already taken.'];
    }

    // ---- Insert ----
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)"
    );
    $stmt->execute([$username, $email, $hash]);

    return ['success' => true, 'user_id' => (int)$pdo->lastInsertId()];
}

/**
 * Attempt to log a user in
 * @return array{success: bool, error?: string, user?: array}
 */
function loginUser(PDO $pdo, string $identifier, string $password): array
{
    $identifier = trim($identifier);

    if ($identifier === '' || $password === '') {
        return ['success' => false, 'error' => 'Please fill in all fields.'];
    }

    $stmt = $pdo->prepare(
        "SELECT id, username, email, password_hash FROM users
         WHERE username = ? OR email = ? LIMIT 1"
    );
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid credentials.'];
    }

    // Rehash if needed (PHP upgrades the algorithm over time)
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $upd->execute([$newHash, $user['id']]);
    }

    // Set session
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    return ['success' => true, 'user' => $user];
}

/**
 * Log out current user
 */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }
    session_destroy();
}
