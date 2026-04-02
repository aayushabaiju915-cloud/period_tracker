<?php
// ============================================================
// pages/register.php — User Registration
// ============================================================
require_once '../config/db.php';
require_once '../config/auth.php';

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: /period_tracker/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $name     = trim($_POST['name']             ?? '');
        $email    = trim($_POST['email']            ?? '');
        $password = trim($_POST['password']         ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');
        $dob      = trim($_POST['dob']              ?? '') ?: null;

        // Server-side validation
        if (strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if email already exists
            $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = 'An account with this email already exists.';
            } else {
                // Hash password and insert user
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare(
                    'INSERT INTO users (name, email, password, dob) VALUES (?, ?, ?, ?)'
                );
                $stmt->bind_param('ssss', $name, $email, $hash, $dob);

                if ($stmt->execute()) {
                    $stmt->close();
                    header('Location: /period_tracker/index.php?msg=registered');
                    exit;
                } else {
                    $error = 'Registration failed. Please try again.';
                }
                $stmt->close();
            }
            $check->close();
        }
    }
}

$pageTitle = 'Register';
include '../includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>Create Account 🌸</h2>
        <p class="auth-sub">Start tracking your cycle today</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:10px;margin-bottom:18px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form id="registerForm" method="POST"
              action="/period_tracker/pages/register.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       value="<?= clean($_POST['name'] ?? '') ?>"
                       placeholder="Your name">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= clean($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth <span class="text-muted">(optional)</span></label>
                <input type="date" id="dob" name="dob"
                       value="<?= clean($_POST['dob'] ?? '') ?>"
                       max="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min 8 characters">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full mt-2">
                🌸 Create Account
            </button>
        </form>

        <p class="text-center mt-2 text-muted" style="font-size:.9rem;">
            Already have an account?
            <a href="/period_tracker/index.php" class="fw-600">Log in</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
