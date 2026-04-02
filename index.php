<?php
// ============================================================
// index.php — Login Page
// ============================================================
require_once 'config/db.php';
require_once 'config/auth.php';

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: /period_tracker/pages/dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            $error = 'Both email and password are required.';
        } else {
            // Fetch user by email
            $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['flash']     = ['type' => 'success', 'msg' => 'Welcome back, ' . $user['name'] . '! 🌸'];
                header('Location: /period_tracker/pages/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        }
    }
}

// Handle query-string messages
$qMsg = $_GET['msg'] ?? '';
$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>Welcome Back 🌸</h2>
        <p class="auth-sub">Log in to your FlowTrack account</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:10px;margin-bottom:18px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($qMsg === 'login_required'): ?>
            <div class="flash flash-info" style="border-radius:10px;margin-bottom:18px;">
                Please log in to continue.
            </div>
        <?php elseif ($qMsg === 'logged_out'): ?>
            <div class="flash flash-success" style="border-radius:10px;margin-bottom:18px;">
                You have been logged out successfully.
            </div>
        <?php elseif ($qMsg === 'registered'): ?>
            <div class="flash flash-success" style="border-radius:10px;margin-bottom:18px;">
                Account created! Please log in.
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="/period_tracker/index.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= clean($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-full mt-2">
                🌸 Log In
            </button>
        </form>

        <p class="text-center mt-2 text-muted" style="font-size:.9rem;">
            Don't have an account?
            <a href="/period_tracker/pages/register.php" class="fw-600">Register here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
