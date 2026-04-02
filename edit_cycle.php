<?php
// ============================================================
// pages/edit_cycle.php — Edit an Existing Cycle (UPDATE)
// ============================================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireLogin();

$userId  = currentUserId();
$cycleId = (int)($_GET['id'] ?? 0);
$error   = '';

// ── Fetch existing cycle (must belong to user) ─────────────
$stmt = $conn->prepare(
    'SELECT * FROM cycles WHERE id = ? AND user_id = ? LIMIT 1'
);
$stmt->bind_param('ii', $cycleId, $userId);
$stmt->execute();
$cycle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cycle) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Cycle not found.'];
    header('Location: /period_tracker/pages/cycles.php');
    exit;
}

// ── Handle Update ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $startDate    = trim($_POST['start_date']    ?? '');
        $endDate      = trim($_POST['end_date']      ?? '') ?: null;
        $cycleLength  = (int)($_POST['cycle_length']  ?? 28);
        $periodLength = (int)($_POST['period_length'] ?? 5);
        $notes        = trim($_POST['notes']          ?? '');

        if (!$startDate) {
            $error = 'Start date is required.';
        } elseif ($endDate && $endDate < $startDate) {
            $error = 'End date cannot be before the start date.';
        } elseif ($cycleLength < 15 || $cycleLength > 45) {
            $error = 'Cycle length must be between 15 and 45 days.';
        } elseif ($periodLength < 1 || $periodLength > 10) {
            $error = 'Period length must be between 1 and 10 days.';
        } else {
            $upd = $conn->prepare(
                'UPDATE cycles
                 SET start_date=?, end_date=?, cycle_length=?, period_length=?, notes=?
                 WHERE id=? AND user_id=?'
            );
            $upd->bind_param('ssiisii',
                $startDate, $endDate, $cycleLength, $periodLength, $notes,
                $cycleId, $userId
            );

            if ($upd->execute() && $upd->affected_rows >= 0) {
                $upd->close();
                $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cycle updated successfully! ✏️'];
                header('Location: /period_tracker/pages/cycles.php');
                exit;
            } else {
                $error = 'Update failed. Please try again.';
                $upd->close();
            }
        }

        // Merge POST into $cycle so form retains new values on error
        $cycle = array_merge($cycle, [
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'cycle_length'  => $cycleLength,
            'period_length' => $periodLength,
            'notes'         => $notes,
        ]);
    }
}

$pageTitle = 'Edit Cycle';
include '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Edit Cycle ✏️</h1>
        <p>Update your cycle record</p>
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
          action="/period_tracker/pages/edit_cycle.php?id=<?= $cycleId ?>"
          novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Period Start Date *</label>
                <input type="date" id="start_date" name="start_date"
                       value="<?= clean($cycle['start_date']) ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">Period End Date</label>
                <input type="date" id="end_date" name="end_date"
                       value="<?= clean($cycle['end_date'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cycle_length">Cycle Length (days) *</label>
                <input type="number" id="cycle_length" name="cycle_length"
                       value="<?= (int)$cycle['cycle_length'] ?>"
                       min="15" max="45" required>
            </div>
            <div class="form-group">
                <label for="period_length">Period Length (days) *</label>
                <input type="number" id="period_length" name="period_length"
                       value="<?= (int)$cycle['period_length'] ?>"
                       min="1" max="10" required>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes"
                      placeholder="Any additional notes..."><?=
                clean($cycle['notes'] ?? '')
            ?></textarea>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary">
                ✅ Update Cycle
            </button>
            <a href="/period_tracker/pages/cycles.php" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
