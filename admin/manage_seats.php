<?php
/**
 * HackCommand – Manage Seats (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$csrf = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate') {
        $hallId = (int) ($_POST['hall_id'] ?? 0);
        if ($hallId) {
            // Get hall info
            $stmt = $pdo->prepare("SELECT * FROM halls WHERE id = ?");
            $stmt->execute([$hallId]);
            $hall = $stmt->fetch();
            if ($hall) {
                // Delete existing seats for this hall
                $pdo->prepare("DELETE FROM seats WHERE hall_id = ?")->execute([$hallId]);

                // Generate seats
                $prefix = strtoupper(substr($hall['hall_name'], 0, 1));
                $insert = $pdo->prepare("INSERT INTO seats (hall_id, seat_number) VALUES (?, ?)");
                for ($i = 1; $i <= $hall['capacity']; $i++) {
                    $insert->execute([$hallId, $prefix . '-' . $i]);
                }
                setFlash('success', $hall['capacity'] . ' seats generated for ' . $hall['hall_name'] . '.');
            }
        }
    } elseif ($action === 'assign_seat') {
        $seatId = (int) ($_POST['seat_id'] ?? 0);
        $userId = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;
        if ($seatId) {
            $pdo->prepare("UPDATE seats SET user_id = ? WHERE id = ?")->execute([$userId, $seatId]);
            setFlash('success', 'Seat assignment updated.');
        }
    }
    redirect(BASE_URL . '/admin/manage_seats.php' . (isset($_POST['hall_id']) ? '?hall=' . (int) $_POST['hall_id'] : ''));
}

$halls = $pdo->query("SELECT * FROM halls ORDER BY hall_name")->fetchAll();
$selectedHall = isset($_GET['hall']) ? (int) $_GET['hall'] : ($halls[0]['id'] ?? 0);

// Get seats for selected hall
$seats = [];
$hallInfo = null;
if ($selectedHall) {
    $stmt = $pdo->prepare("SELECT * FROM halls WHERE id = ?");
    $stmt->execute([$selectedHall]);
    $hallInfo = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT s.*, u.full_name, u.email
        FROM seats s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.hall_id = ?
        ORDER BY s.seat_number
    ");
    $stmt->execute([$selectedHall]);
    $seats = $stmt->fetchAll();
}

// Get all students for dropdown
$students = $pdo->query("
    SELECT u.id, u.full_name, t.team_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    LEFT JOIN teams t ON u.team_id = t.id
    WHERE r.role_name = 'student'
    ORDER BY u.full_name
")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-grid-3x3-gap-fill me-2"></i>Seating Management</h4>
            </div>

            <?= showFlash() ?>

            <!-- Hall Selection + Generate -->
            <div class="card content-card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Select Hall</label>
                            <select class="form-select" id="hallSelect" onchange="window.location='?hall='+this.value">
                                <?php foreach ($halls as $h): ?>
                                <option value="<?= $h['id'] ?>" <?= $selectedHall == $h['id'] ? 'selected' : '' ?>><?= sanitize($h['hall_name']) ?> (Cap: <?= $h['capacity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="action" value="generate">
                                <input type="hidden" name="hall_id" value="<?= $selectedHall ?>">
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('This will regenerate all seats and clear existing assignments. Continue?')">
                                    <i class="bi bi-arrow-repeat me-1"></i>Auto-Generate Seats
                                </button>
                            </form>
                        </div>
                        <div class="col-md-3 text-end">
                            <?php if ($hallInfo): ?>
                            <span class="badge bg-primary fs-6"><?= count($seats) ?> / <?= $hallInfo['capacity'] ?> seats</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Seating Grid -->
            <?php if (!empty($seats)): ?>
            <div class="card content-card mb-4">
                <div class="card-header"><i class="bi bi-grid-3x3 me-2"></i>Seating Layout – <?= sanitize($hallInfo['hall_name']) ?></div>
                <div class="card-body">
                    <div class="d-flex gap-3 mb-3">
                        <span><span class="d-inline-block rounded" style="width:16px;height:16px;background:#e9ecef;"></span> Empty</span>
                        <span><span class="d-inline-block rounded" style="width:16px;height:16px;background:#cfe2ff;"></span> Occupied</span>
                    </div>
                    <div class="seating-grid">
                        <?php foreach ($seats as $seat): ?>
                        <div class="seat-cell <?= $seat['user_id'] ? 'seat-occupied' : 'seat-empty' ?>"
                             data-bs-toggle="tooltip" title="<?= $seat['full_name'] ? sanitize($seat['full_name']) : 'Empty' ?>">
                            <?= sanitize($seat['seat_number']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Seat Assignment Table -->
            <div class="card content-card">
                <div class="card-header"><i class="bi bi-list-check me-2"></i>Assign Students to Seats</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Seat #</th><th>Assigned To</th><th>Assign / Change</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($seats as $seat): ?>
                            <tr>
                                <td><strong><?= sanitize($seat['seat_number']) ?></strong></td>
                                <td>
                                    <?php if ($seat['full_name']): ?>
                                    <span class="badge bg-primary"><?= sanitize($seat['full_name']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">— Empty —</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="action" value="assign_seat">
                                        <input type="hidden" name="seat_id" value="<?= $seat['id'] ?>">
                                        <input type="hidden" name="hall_id" value="<?= $selectedHall ?>">
                                        <select name="user_id" class="form-select form-select-sm" style="max-width: 250px;">
                                            <option value="">— Clear —</option>
                                            <?php foreach ($students as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= $seat['user_id'] == $s['id'] ? 'selected' : '' ?>>
                                                <?= sanitize($s['full_name']) ?><?= $s['team_name'] ? ' (' . sanitize($s['team_name']) . ')' : '' ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php elseif ($hallInfo): ?>
            <div class="card content-card">
                <div class="card-body empty-state">
                    <i class="bi bi-grid-3x3-gap d-block"></i>
                    <p>No seats generated yet. Click "Auto-Generate Seats" to create seats for this hall.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
