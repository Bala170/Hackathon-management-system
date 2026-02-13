<?php
/**
 * HackCommand – Assign Halls to Teams (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$csrf = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'assign') {
        $teamId = (int) ($_POST['team_id'] ?? 0);
        $hallId = (int) ($_POST['hall_id'] ?? 0);
        if ($teamId && $hallId) {
            // Upsert: if team already assigned, update; otherwise insert
            $stmt = $pdo->prepare("SELECT id FROM hall_assignments WHERE team_id = ?");
            $stmt->execute([$teamId]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE hall_assignments SET hall_id = ? WHERE team_id = ?")->execute([$hallId, $teamId]);
            } else {
                $pdo->prepare("INSERT INTO hall_assignments (team_id, hall_id) VALUES (?, ?)")->execute([$teamId, $hallId]);
            }
            setFlash('success', 'Team assigned to hall successfully.');
        }
    } elseif ($action === 'unassign') {
        $teamId = (int) ($_POST['team_id'] ?? 0);
        if ($teamId) {
            $pdo->prepare("DELETE FROM hall_assignments WHERE team_id = ?")->execute([$teamId]);
            setFlash('success', 'Team unassigned from hall.');
        }
    }
    redirect(BASE_URL . '/admin/assign_halls.php');
}

$teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();
$halls = $pdo->query("SELECT * FROM halls ORDER BY hall_name")->fetchAll();

// Current assignments
$assignments = $pdo->query("
    SELECT ha.*, t.team_name, h.hall_name, h.capacity, h.location_url
    FROM hall_assignments ha
    JOIN teams t ON ha.team_id = t.id
    JOIN halls h ON ha.hall_id = h.id
    ORDER BY h.hall_name, t.team_name
")->fetchAll();

// Unassigned teams
$assignedTeamIds = array_column($assignments, 'team_id');
$unassignedTeams = array_filter($teams, fn($t) => !in_array($t['id'], $assignedTeamIds));
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-signpost-split me-2"></i>Assign Halls to Teams</h4>
            </div>

            <?= showFlash() ?>

            <!-- Assignment Form -->
            <div class="card content-card mb-4">
                <div class="card-body">
                    <form method="POST" class="row g-3 align-items-end">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="action" value="assign">
                        <div class="col-md-4">
                            <label class="form-label">Select Team</label>
                            <select name="team_id" class="form-select" required>
                                <option value="">— Choose Team —</option>
                                <?php foreach ($teams as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= sanitize($t['team_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Assign to Hall</label>
                            <select name="hall_id" class="form-select" required>
                                <option value="">— Choose Hall —</option>
                                <?php foreach ($halls as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= sanitize($h['hall_name']) ?> (Cap: <?= $h['capacity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle-fill me-1"></i>Assign</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Assignments -->
            <div class="card content-card">
                <div class="card-header"><i class="bi bi-list-check me-2"></i>Current Hall Assignments</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>Team</th><th>Hall</th><th>Capacity</th><th>Map</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($assignments as $i => $a): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= sanitize($a['team_name']) ?></strong></td>
                                    <td><span class="badge bg-info"><?= sanitize($a['hall_name']) ?></span></td>
                                    <td><?= $a['capacity'] ?></td>
                                    <td>
                                        <?php if ($a['location_url']): ?>
                                        <a href="<?= sanitize($a['location_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-geo-alt-fill"></i></a>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="action" value="unassign">
                                            <input type="hidden" name="team_id" value="<?= $a['team_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-x-circle-fill me-1"></i>Unassign</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assignments)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No assignments yet</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
