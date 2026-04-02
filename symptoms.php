<?php
// ============================================================
// pages/symptoms.php — Symptom / Mood Log (CRUD)
// ============================================================
require_once '../config/db.php';
require_once '../config/auth.php';
requireLogin();

$userId = currentUserId();
$error  = '';

// ── Fetch user's cycles (for dropdown) ─────────────────────
$cycleList = [];
$cRes = $conn->prepare(
    'SELECT id, start_date, end_date FROM cycles
     WHERE user_id = ? ORDER BY start_date DESC'
);
$cRes->bind_param('i', $userId);
$cRes->execute();
$cResult = $cRes->get_result();
while ($row = $cResult->fetch_assoc()) $cycleList[] = $row;
$cRes->close();

// ── Handle Delete ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_symptom_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid request.'];
    } else {
        $delId = (int)$_POST['delete_symptom_id'];
        $del = $conn->prepare(
            'DELETE FROM symptoms WHERE id = ? AND user_id = ?'
        );
        $del->bind_param('ii', $delId, $userId);
        $del->execute();
        $del->close();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Symptom log deleted.'];
    }
    header('Location: /period_tracker/pages/symptoms.php');
    exit;
}

// ── Handle Add Symptom ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_symptom'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $cycleId  = (int)($_POST['cycle_id'] ?? 0);
        $logDate  = trim($_POST['log_date']  ?? '');
        $mood     = trim($_POST['mood']      ?? 'neutral');
        $flow     = trim($_POST['flow']      ?? 'none');
        $cramps   = isset($_POST['cramps'])   ? 1 : 0;
        $headache = isset($_POST['headache']) ? 1 : 0;
        $bloating = isset($_POST['bloating']) ? 1 : 0;
        $fatigue  = isset($_POST['fatigue'])  ? 1 : 0;
        $notes    = trim($_POST['notes']      ?? '');

        if (!$logDate || !$cycleId) {
            $error = 'Please select a cycle and a date.';
        } else {
            // Verify cycle belongs to user
            $verify = $conn->prepare(
                'SELECT id FROM cycles WHERE id = ? AND user_id = ? LIMIT 1'
            );
            $verify->bind_param('ii', $cycleId, $userId);
            $verify->execute();
            $verify->store_result();
            $valid = $verify->num_rows > 0;
            $verify->close();

            if (!$valid) {
                $error = 'Invalid cycle selected.';
            } else {
                $ins = $conn->prepare(
                    'INSERT INTO symptoms
                        (user_id, cycle_id, log_date, mood, flow, cramps, headache, bloating, fatigue, notes)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $ins->bind_param('iisssiiis',
                    $userId, $cycleId, $logDate, $mood, $flow,
                    $cramps, $headache, $bloating, $fatigue, $notes
                );
                if ($ins->execute()) {
                    $ins->close();
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Symptom log saved! 😊'];
                    header('Location: /period_tracker/pages/symptoms.php');
                    exit;
                } else {
                    $error = 'Failed to save. Please try again.';
                    $ins->close();
                }
            }
        }
    }
}

// ── Fetch All Symptoms ─────────────────────────────────────
$symptoms = [];
$sRes = $conn->prepare(
    'SELECT s.*, c.start_date AS cycle_start
     FROM symptoms s
     JOIN cycles c ON s.cycle_id = c.id
     WHERE s.user_id = ?
     ORDER BY s.log_date DESC'
);
$sRes->bind_param('i', $userId);
$sRes->execute();
$sResult = $sRes->get_result();
while ($row = $sResult->fetch_assoc()) $symptoms[] = $row;
$sRes->close();

$moodEmoji = [
    'happy' => '😊', 'neutral' => '😐', 'sad' => '😢',
    'anxious' => '😰', 'irritable' => '😤', 'energetic' => '⚡'
];

$pageTitle = 'Symptoms';
include '../includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Symptoms & Mood 😊</h1>
        <p>Track daily symptoms during your cycle</p>
    </div>
</div>

<!-- ── Add Symptom Form ───────────────────────────────────── -->
<div class="card mb-3">
    <h2 style="font-size:1.2rem;margin-bottom:18px;">Log Today's Symptoms</h2>

    <?php if ($error): ?>
        <div class="flash flash-error" style="border-radius:10px;margin-bottom:14px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cycleList)): ?>
        <div class="flash flash-info" style="border-radius:10px;">
            You need to <a href="/period_tracker/pages/add_cycle.php">add a cycle</a> first
            before logging symptoms.
        </div>
    <?php else: ?>
    <form id="symptomForm" method="POST"
          action="/period_tracker/pages/symptoms.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="add_symptom" value="1">

        <div class="form-row">
            <div class="form-group">
                <label for="cycle_id">Select Cycle *</label>
                <select id="cycle_id" name="cycle_id" required>
                    <option value="">— Choose cycle —</option>
                    <?php foreach ($cycleList as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= date('d M Y', strtotime($c['start_date'])) ?>
                            <?= $c['end_date']
                                ? ' → ' . date('d M Y', strtotime($c['end_date']))
                                : ' (ongoing)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="log_date">Date *</label>
                <input type="date" id="log_date" name="log_date"
                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="mood">Mood</label>
                <select id="mood" name="mood">
                    <option value="happy">😊 Happy</option>
                    <option value="neutral" selected>😐 Neutral</option>
                    <option value="sad">😢 Sad</option>
                    <option value="anxious">😰 Anxious</option>
                    <option value="irritable">😤 Irritable</option>
                    <option value="energetic">⚡ Energetic</option>
                </select>
            </div>
            <div class="form-group">
                <label for="flow">Flow Intensity</label>
                <select id="flow" name="flow">
                    <option value="none">— None</option>
                    <option value="spotting">Spotting</option>
                    <option value="light">Light</option>
                    <option value="medium" selected>Medium</option>
                    <option value="heavy">Heavy</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Symptoms (check all that apply)</label>
            <div class="symptom-grid">
                <label class="symptom-chip">
                    <input type="checkbox" name="cramps"> 🤕 Cramps
                </label>
                <label class="symptom-chip">
                    <input type="checkbox" name="headache"> 🤯 Headache
                </label>
                <label class="symptom-chip">
                    <input type="checkbox" name="bloating"> 🎈 Bloating
                </label>
                <label class="symptom-chip">
                    <input type="checkbox" name="fatigue"> 😴 Fatigue
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea id="notes" name="notes"
                      placeholder="Anything else you'd like to note..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            😊 Save Symptom Log
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- ── Symptom History ─────────────────────────────────────── -->
<div class="card">
    <h2 style="font-size:1.2rem;margin-bottom:16px;">Symptom History</h2>

    <?php if (empty($symptoms)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No symptoms logged yet</h3>
            <p>Start tracking to see your history here.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Cycle</th>
                        <th>Mood</th>
                        <th>Flow</th>
                        <th>Cramps</th>
                        <th>Headache</th>
                        <th>Bloating</th>
                        <th>Fatigue</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($symptoms as $s): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($s['log_date'])) ?></td>
                        <td class="text-muted" style="font-size:.83rem;">
                            <?= date('d M', strtotime($s['cycle_start'])) ?>
                        </td>
                        <td><?= $moodEmoji[$s['mood']] ?? '' ?>
                            <span style="font-size:.83rem;"><?= ucfirst($s['mood']) ?></span>
                        </td>
                        <td>
                            <?php
                            $flowBadge = [
                                'none' => 'badge-purple', 'spotting' => 'badge-amber',
                                'light' => 'badge-green', 'medium' => 'badge-rose',
                                'heavy' => 'badge-rose'
                            ];
                            ?>
                            <span class="badge <?= $flowBadge[$s['flow']] ?? 'badge-rose' ?>">
                                <?= ucfirst($s['flow']) ?>
                            </span>
                        </td>
                        <td><?= $s['cramps']   ? '✅' : '–' ?></td>
                        <td><?= $s['headache'] ? '✅' : '–' ?></td>
                        <td><?= $s['bloating'] ? '✅' : '–' ?></td>
                        <td><?= $s['fatigue']  ? '✅' : '–' ?></td>
                        <td style="font-size:.83rem;max-width:140px;">
                            <?= $s['notes'] ? htmlspecialchars(mb_strimwidth($s['notes'], 0, 40, '…')) : '–' ?>
                        </td>
                        <td>
                            <form method="POST"
                                  action="/period_tracker/pages/symptoms.php"
                                  style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_symptom_id" value="<?= $s['id'] ?>">
                                <button type="submit"
                                        class="btn btn-danger btn-sm"
                                        data-confirm="Delete this symptom log?">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
