<?php
/**
 * HackCommand – Scoring (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$csrf = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_score') {
        $teamId     = (int) ($_POST['team_id'] ?? 0);
        $judgeName  = trim($_POST['judge_name'] ?? '');
        $innovation = floatval($_POST['innovation'] ?? 0);
        $technical  = floatval($_POST['technical'] ?? 0);
        $uiUx       = floatval($_POST['ui_ux'] ?? 0);
        $impact     = floatval($_POST['impact'] ?? 0);

        if ($teamId && $judgeName) {
            // Check for duplicate
            $stmt = $pdo->prepare("SELECT id FROM scores WHERE team_id = ? AND judge_name = ?");
            $stmt->execute([$teamId, $judgeName]);
            if ($stmt->fetch()) {
                setFlash('danger', 'This judge has already scored this team. Duplicate scoring prevented.');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO scores (team_id, judge_name, innovation, technical, ui_ux, impact, scored_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$teamId, $judgeName, $innovation, $technical, $uiUx, $impact, $_SESSION['user_id']]);
                setFlash('success', 'Score recorded successfully.');
            }
        }
    } elseif ($action === 'delete_score') {
        $scoreId = (int) ($_POST['score_id'] ?? 0);
        if ($scoreId) {
            $pdo->prepare("DELETE FROM scores WHERE id = ?")->execute([$scoreId]);
            setFlash('success', 'Score deleted.');
        }
    }
    redirect(BASE_URL . '/admin/scoring.php');
}

$teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();

// Scores grouped by team
$scores = $pdo->query("
    SELECT s.*, t.team_name
    FROM scores s
    JOIN teams t ON s.team_id = t.id
    ORDER BY t.team_name, s.judge_name
")->fetchAll();

// Group scores by team
$grouped = [];
foreach ($scores as $s) {
    $grouped[$s['team_id']]['team_name'] = $s['team_name'];
    $grouped[$s['team_id']]['scores'][] = $s;
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-star-fill me-2"></i>Judge Scoring</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScoreModal">
                    <i class="bi bi-plus-circle-fill me-1"></i>Add Score
                </button>
            </div>

            <?= showFlash() ?>

            <!-- Scoring Criteria Reference -->
            <div class="alert alert-info mb-4">
                <strong>Scoring Criteria (0–10 each):</strong>
                Innovation &middot; Technical Implementation &middot; UI/UX &middot; Impact
                <strong class="ms-3">Max Total: 40</strong>
            </div>

            <?php if (empty($grouped)): ?>
            <div class="card content-card"><div class="card-body empty-state"><i class="bi bi-star d-block"></i><p>No scores recorded yet</p></div></div>
            <?php else: ?>
            <?php foreach ($grouped as $teamId => $data): ?>
            <?php
                $teamScores = $data['scores'];
                $avgTotal = array_sum(array_column($teamScores, 'total')) / count($teamScores);
            ?>
            <div class="card content-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-people-fill me-2"></i><?= sanitize($data['team_name']) ?></strong>
                    <span class="badge bg-primary fs-6">Avg: <?= number_format($avgTotal, 1) ?> / 40</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Judge</th>
                                    <th class="text-center">Innovation</th>
                                    <th class="text-center">Technical</th>
                                    <th class="text-center">UI/UX</th>
                                    <th class="text-center">Impact</th>
                                    <th class="text-center">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($teamScores as $sc): ?>
                            <tr>
                                <td><strong><?= sanitize($sc['judge_name']) ?></strong></td>
                                <td class="text-center"><?= $sc['innovation'] ?></td>
                                <td class="text-center"><?= $sc['technical'] ?></td>
                                <td class="text-center"><?= $sc['ui_ux'] ?></td>
                                <td class="text-center"><?= $sc['impact'] ?></td>
                                <td class="text-center"><span class="badge bg-dark"><?= $sc['total'] ?></span></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="action" value="delete_score">
                                        <input type="hidden" name="score_id" value="<?= $sc['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash-fill"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Score Modal -->
<div class="modal fade" id="addScoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-star-fill me-2"></i>Add Score</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="add_score">
                    <div class="mb-3">
                        <label class="form-label">Team</label>
                        <select name="team_id" class="form-select" required>
                            <option value="">— Select Team —</option>
                            <?php foreach ($teams as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= sanitize($t['team_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judge Name</label>
                        <input type="text" name="judge_name" class="form-control" required placeholder="e.g., Dr. Patel">
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Innovation (0–10)</label>
                            <input type="number" name="innovation" class="form-control" min="0" max="10" step="0.5" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Technical (0–10)</label>
                            <input type="number" name="technical" class="form-control" min="0" max="10" step="0.5" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">UI/UX (0–10)</label>
                            <input type="number" name="ui_ux" class="form-control" min="0" max="10" step="0.5" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Impact (0–10)</label>
                            <input type="number" name="impact" class="form-control" min="0" max="10" step="0.5" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Submit Score</button></div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
