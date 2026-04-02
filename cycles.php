<?php
// ============================================================
// pages/cycles.php — View All Cycles (READ)
// ============================================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireLogin();

$userId = currentUserId();

// ── Handle Delete ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid request.'];
    } else {
        $deleteId = (int)$_POST['delete_id'];
        $stmt = $conn->prepare(
            'DELETE FROM cycles WHERE id = ? AND user_id = ?'
        );
        $stmt->bind_param('ii', $deleteId, $userId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cycle deleted successfully.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Could not delete cycle.'];
        }
        $stmt->close();
    }
    header('Location: /period_tracker/pages/cycles.php');
    exit;
}

// ── Fetch All Cycles ───────────────────────────────────────
$cycles = [];
$res = $conn->prepare(
    'SELECT * FROM cycles WHERE user_id = ? ORDER BY start_date DESC'
);
$res->bind_param('i', $userId);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc()) $cycles[] = $row;
$res->close();

// ── Prediction ─────────────────────────────────────────────
$predicted = predictNextPeriod($cycles);

$pageTitle = 'My Cycles';
include '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>My Cycles 📅</h1>
        <p>Complete cycle history</p>
    </div>
    <a href="/period_tracker/pages/add_cycle.php" class="btn btn-primary">
        + Add Cycle
    </a>
</div>

<?php if ($predicted): ?>
<div class="predict-banner mb-3">
    <span class="predict-icon">🔮</span>
    <div>
        <h3>Predicted Next Period</h3>
        <p><strong><?= date('D, d M Y', strtotime($predicted)) ?></strong>
           &nbsp;(<?= daysUntil($predicted) ?> days away)</p>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <?php if (empty($cycles)): ?>
        <div class="empty-state">
            <div class="empty-icon">🌸</div>
            <h3>No cycles logged yet</h3>
            <p>Add your first cycle to start tracking.</p>
            <a href="/period_tracker/pages/add_cycle.php" class="btn btn-primary mt-2">
                + Add First Cycle
            </a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Cycle Length</th>
                        <th>Period Length</th>
                        <th>Notes</th>
                        <th>Logged On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cycles as $i => $c): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td><?= date('d M Y', strtotime($c['start_date'])) ?></td>
                        <td>
                            <?= $c['end_date']
                                ? date('d M Y', strtotime($c['end_date']))
                                : '<span class="text-muted">Ongoing</span>' ?>
                        </td>
                        <td><span class="badge badge-rose"><?= $c['cycle_length'] ?> days</span></td>
                        <td><span class="badge badge-green"><?= $c['period_length'] ?> days</span></td>
                        <td style="max-width:180px;">
                            <?= $c['notes']
                                ? '<span title="' . htmlspecialchars($c['notes']) . '">'
                                  . htmlspecialchars(mb_strimwidth($c['notes'], 0, 40, '…'))
                                  . '</span>'
                                : '<span class="text-muted">–</span>' ?>
                        </td>
                        <td class="text-muted" style="font-size:.83rem;">
                            <?= date('d M Y', strtotime($c['created_at'])) ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <!-- Edit -->
                            <a href="/period_tracker/pages/edit_cycle.php?id=<?= $c['id'] ?>"
                               class="btn btn-secondary btn-sm">✏️ Edit</a>

                            <!-- Delete -->
                            <form method="POST"
                                  action="/period_tracker/pages/cycles.php"
                                  style="display:inline;">
                                <input type="hidden" name="csrf_token"
                                       value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id"
                                       value="<?= $c['id'] ?>">
                                <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        data-confirm="Delete this cycle record? Associated symptoms will also be removed.">
                                    🗑️ Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted mt-2" style="font-size:.82rem;">
            Total: <?= count($cycles) ?> cycle(s) logged
        </p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
