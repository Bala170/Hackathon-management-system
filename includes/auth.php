<?php
/**
 * HackCommand – Authentication & Session Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout: 30 minutes
define('SESSION_TIMEOUT', 1800);

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/auth/login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

/**
 * Require a specific role to access a page.
 * Redirects to login if not authenticated or wrong role.
 */
function requireRole(string $role): void
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit;
    }
    if ($_SESSION['role'] !== $role) {
        header("Location: " . BASE_URL . "/auth/login.php?error=unauthorized");
        exit;
    }
}

/**
 * Check if any user is logged in (any role).
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit;
    }
}

/**
 * Generate a CSRF token and store in session.
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from POST data.
 */
function validateCsrfToken(): bool
{
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
