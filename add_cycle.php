<?php
// ============================================================
// pages/add_cycle.php — Add New Cycle (CREATE)
// ============================================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireLogin();

$userId = currentUserId();
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $startDate    = trim($_POST['start_date']    ?? '');
        $endDate      = trim($_POST['end_date']      ?? '') ?: null;
        $cycleLength  = (int)($_POST['cycle_length']  ?? 28);
        $periodLength = (int)($_POST['period_length'] ?? 5);
        $notes        = trim($_POST['notes']          ?? '');

        // Validation
        if (!$startDate) {
            $error = 'Start date is required.';
        } elseif ($endDate && $endDate < $startDate) {
            $error = 'End date cannot be before the start date.';
        } elseif ($cycleLength < 15 || $cycleLength > 45) {
            $error = 'Cycle length must be between 15 and 45 days.';
        } elseif ($periodLength < 1 || $periodLength > 10) {
            $error = 'Period length must be between 1 and 10 days.';
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO cycles
                    (user_id, start_date, end_date, cycle_length, period_length, notes)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ississ', $userId, $startDate, $endDate,
                                         $cycleLength, $periodLength, $notes);

            if ($stmt->execute()) {
                $stmt->close();
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cycle added successfully! 🌸'];
                header('Location: /period_tracker/pages/cycles.php');
                exit;
            } else {
                $error = 'Failed to save cycle. Please try again.';
                $stmt->close();
            }
        }
    }
}

$pageTitle = 'Add Cycle';
include '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Log New Cycle 📅</h1>
        <p>Record your period start date and details</p>
    </div>
    <a href="/period_tracker/pages/cycles.php" class="btn btn-secondary">
        ← Back to Cycles
    </a>
</div>

<div class="card" style="max-width:640px;">
    <?php if ($error): ?>
        <div class="flash flash-error" style="border-radius:10px;margin-bottom:18px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form id="cycleForm" method="POST"
          action="/period_tracker/pages/add_cycle.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Period Start Date *</label>
                <input type="date" id="start_date" name="start_date"
                       value="<?= clean($_POST['start_date'] ?? '') ?>"
                       max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">Period End Date</label>
                <input type="date" id="end_date" name="end_date"
                       value="<?= clean($_POST['end_date'] ?? '') ?>"
                       max="<?= date('Y-m-d') ?>">
                <span style="font-size:.8rem;color:var(--ink-muted);">
                    Leave blank if still ongoing
                </span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cycle_length">
                    Cycle Length (days) *
                    <span class="text-muted" style="font-weight:400;">(15–45)</span>
                </label>
                <input type="number" id="cycle_length" name="cycle_length"
                       value="<?= (int)($_POST['cycle_length'] ?? 28) ?>"
                       min="15" max="45" required>
                <span style="font-size:.8rem;color:var(--ink-muted);">
                    Days from first day of one period to next
                </span>
            </div>
            <div class="form-group">
                <label for="period_length">
                    Period Length (days) *
                    <span class="text-muted" style="font-weight:400;">(1–10)</span>
                </label>
                <input type="number" id="period_length" name="period_length"
                       value="<?= (int)($_POST['period_length'] ?? 5) ?>"
                       min="1" max="10" required>
                <span style="font-size:.8rem;color:var(--ink-muted);">
                    How many days your period lasts
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes <span class="text-muted" style="font-weight:400;">(optional)</span></label>
            <textarea id="notes" name="notes"
                      placeholder="e.g. heavy flow, mild cramps, stress..."><?=
                clean($_POST['notes'] ?? '')
            ?></textarea>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">
                🌸 Save Cycle
            </button>
            <a href="/period_tracker/pages/cycles.php" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
