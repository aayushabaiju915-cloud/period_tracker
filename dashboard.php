<?php
// ============================================================
// pages/dashboard.php — Main Dashboard After Login
// Shows stats, prediction, and recent cycles
// ============================================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireLogin();

$userId = currentUserId();

// ── Fetch all cycles (ordered newest first) ────────────────
$cycles = [];
$res = $conn->prepare(
    'SELECT * FROM cycles WHERE user_id = ? ORDER BY start_date DESC'
);
$res->bind_param('i', $userId);
$res->execute();
$cycleResult = $res->get_result();
while ($row = $cycleResult->fetch_assoc()) $cycles[] = $row;
$res->close();

// ── Stats ──────────────────────────────────────────────────
$totalCycles   = count($cycles);
$avgCycleLen   = $totalCycles > 0
    ? round(array_sum(array_column($cycles, 'cycle_length')) / $totalCycles)
    : 28;

// Average period length (exclude nulls / zeros)
$periodLengths = array_filter(array_column($cycles, 'period_length'));
$avgPeriodLen  = count($periodLengths) > 0
    ? round(array_sum($periodLengths) / count($periodLengths))
    : 5;

// ── Prediction ─────────────────────────────────────────────
$predictedDate = predictNextPeriod($cycles);
$daysLeft      = $predictedDate ? daysUntil($predictedDate) : null;

// ── Recent 3 cycles ────────────────────────────────────────
$recentCycles = array_slice($cycles, 0, 3);

$pageTitle = 'Dashboard';
include '../includes/header.php';
?>

<!-- Page heading -->
<div class="page-head">
    <div>
        <h1>Hello, <?= currentUserName() ?> 🌸</h1>
        <p>Here's your cycle overview</p>
    </div>
    <a href="/period_tracker/pages/add_cycle.php" class="btn btn-primary">
        + Add Cycle
    </a>
</div>

<!-- ── Prediction Banner ───────────────────────────────────── -->
<?php if ($predictedDate): ?>
    <?php
    $bannerClass = '';
    $icon = '🗓️';
    $urgencyMsg = '';
    if ($daysLeft !== null) {
        if ($daysLeft <= 0) {
            $bannerClass = 'soon';
            $icon = '🩸';
            $urgencyMsg = 'Your period may have started or is overdue!';
        } elseif ($daysLeft <= 5) {
            $bannerClass = 'soon';
            $icon = '⚠️';
            $urgencyMsg = 'Only ' . $daysLeft . ' day(s) away — be prepared!';
        } else {
            $urgencyMsg = $daysLeft . ' days away';
        }
    }
    ?>
    <div class="predict-banner <?= $bannerClass ?>">
        <span class="predict-icon"><?= $icon ?></span>
        <div>
            <h3>Next Period Prediction</h3>
            <p>
                <strong><?= date('D, d M Y', strtotime($predictedDate)) ?></strong>
                &nbsp;&mdash;&nbsp; <?= htmlspecialchars($urgencyMsg) ?>
            </p>
            <p style="font-size:.82rem;margin-top:4px;">
                Based on your average cycle length of <strong><?= $avgCycleLen ?> days</strong>
            </p>
        </div>
    </div>
<?php elseif ($totalCycles === 0): ?>
    <div class="predict-banner">
        <span class="predict-icon">📋</span>
        <div>
            <h3>No cycles logged yet</h3>
            <p>Add your first cycle to start tracking and get predictions.</p>
        </div>
    </div>
<?php endif; ?>

<!-- ── Stats Cards ─────────────────────────────────────────── -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalCycles ?></div>
        <div class="stat-label">Cycles Logged</div>
    </div>
    <div class="stat-card green">
        <div class="stat-value"><?= $avgCycleLen ?></div>
        <div class="stat-label">Avg Cycle Length (days)</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-value"><?= $avgPeriodLen ?></div>
        <div class="stat-label">Avg Period Length (days)</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-value"><?= $daysLeft !== null ? ($daysLeft <= 0 ? 'Now' : $daysLeft) : '–' ?></div>
        <div class="stat-label">Days Until Next Period</div>
    </div>
</div>

<!-- ── Recent Cycles ───────────────────────────────────────── -->
<div class="card">
    <div class="page-head" style="margin-bottom:16px;">
        <h2 style="font-size:1.25rem;">Recent Cycles</h2>
        <a href="/period_tracker/pages/cycles.php" class="btn btn-secondary btn-sm">
            View All →
        </a>
    </div>

    <?php if (empty($recentCycles)): ?>
        <div class="empty-state">
            <div class="empty-icon">🌸</div>
            <h3>No cycles logged yet</h3>
            <p>Click "Add Cycle" to log your first period.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Cycle Length</th>
                        <th>Period Length</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentCycles as $c): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($c['start_date'])) ?></td>
                        <td><?= $c['end_date'] ? date('d M Y', strtotime($c['end_date'])) : '<span class="text-muted">–</span>' ?></td>
                        <td><span class="badge badge-rose"><?= $c['cycle_length'] ?> days</span></td>
                        <td><span class="badge badge-green"><?= $c['period_length'] ?> days</span></td>
                        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= $c['notes'] ? htmlspecialchars($c['notes']) : '<span class="text-muted">–</span>' ?>
                        </td>
                        <td>
                            <a href="/period_tracker/pages/edit_cycle.php?id=<?= $c['id'] ?>"
                               class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ── Quick Links ─────────────────────────────────────────── -->
<div class="dashboard-grid mt-3">
    <a href="/period_tracker/pages/add_cycle.php" class="card-sm"
       style="display:block;text-align:center;text-decoration:none;">
        <div style="font-size:2rem;">📅</div>
        <div class="fw-600 mt-1">Log New Cycle</div>
        <div class="text-muted" style="font-size:.85rem;">Record start & end dates</div>
    </a>
    <a href="/period_tracker/pages/symptoms.php" class="card-sm"
       style="display:block;text-align:center;text-decoration:none;">
        <div style="font-size:2rem;">😊</div>
        <div class="fw-600 mt-1">Track Symptoms</div>
        <div class="text-muted" style="font-size:.85rem;">Mood, flow, cramps & more</div>
    </a>
    <a href="/period_tracker/pages/cycles.php" class="card-sm"
       style="display:block;text-align:center;text-decoration:none;">
        <div style="font-size:2rem;">📊</div>
        <div class="fw-600 mt-1">View History</div>
        <div class="text-muted" style="font-size:.85rem;">All past cycle records</div>
    </a>
</div>

<?php include '../includes/footer.php'; ?>
