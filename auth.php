<?php
// ============================================================
// config/auth.php — Session & Authentication Helpers
// Include at the top of any page that requires login.
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login page if user is not authenticated.
 * Call this at the top of every protected page.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /period_tracker/index.php?msg=login_required');
        exit;
    }
}

/**
 * Return the current logged-in user's ID.
 */
function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/**
 * Return the current logged-in user's name.
 */
function currentUserName(): string {
    return htmlspecialchars($_SESSION['user_name'] ?? 'User');
}

/**
 * Destroy session and redirect to login.
 */
function logout(): void {
    session_unset();
    session_destroy();
    header('Location: /period_tracker/index.php?msg=logged_out');
    exit;
}

/**
 * Simple CSRF token generator & validator.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Sanitize string input.
 */
function clean(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate predicted next period date.
 * Uses average of last 3 cycle lengths if available.
 *
 * @param array $cycles  Array of cycle rows (start_date, cycle_length)
 * @return string|null   Predicted date string or null
 */
function predictNextPeriod(array $cycles): ?string {
    if (empty($cycles)) return null;

    // Use up to 3 most-recent cycles for average
    $recent  = array_slice($cycles, 0, 3);
    $lengths = array_column($recent, 'cycle_length');
    $avgLen  = (int)round(array_sum($lengths) / count($lengths));

    $lastStart = $cycles[0]['start_date'];          // most recent
    $predicted = date('Y-m-d', strtotime($lastStart . ' +' . $avgLen . ' days'));
    return $predicted;
}

/**
 * Days until a future date (negative if past).
 */
function daysUntil(string $dateStr): int {
    $today = new DateTime('today');
    $target = new DateTime($dateStr);
    $diff = $today->diff($target);
    return $diff->invert ? -$diff->days : $diff->days;
}
