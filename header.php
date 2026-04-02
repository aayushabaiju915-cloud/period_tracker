<?php
// ============================================================
// includes/header.php — Shared HTML head + nav bar
// Variables expected by caller:
//   $pageTitle (string) — used in <title>
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = !empty($_SESSION['user_id']);
$userName   = $isLoggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Period Tracker' ?> – FlowTrack</title>

    <!-- Google Fonts: Playfair Display + DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/period_tracker/css/style.css">
</head>
<body>

<!-- ── Navigation ─────────────────────────────────────────── -->
<nav class="navbar">
    <a href="/period_tracker/<?= $isLoggedIn ? 'pages/dashboard.php' : 'index.php' ?>" class="nav-brand">
        🌸 FlowTrack
    </a>

    <?php if ($isLoggedIn): ?>
    <div class="nav-links">
        <a href="/period_tracker/pages/dashboard.php">Dashboard</a>
        <a href="/period_tracker/pages/cycles.php">My Cycles</a>
        <a href="/period_tracker/pages/add_cycle.php">+ Add Cycle</a>
        <a href="/period_tracker/pages/symptoms.php">Symptoms</a>
        <span class="nav-user">👤 <?= $userName ?></span>
        <a href="/period_tracker/pages/logout.php" class="btn-logout">Logout</a>
    </div>
    <?php else: ?>
    <div class="nav-links">
        <a href="/period_tracker/index.php">Login</a>
        <a href="/period_tracker/pages/register.php">Register</a>
    </div>
    <?php endif; ?>
</nav>

<!-- ── Flash Message ──────────────────────────────────────── -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash flash-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<main class="container">
