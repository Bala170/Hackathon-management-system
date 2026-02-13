<?php
/**
 * HackCommand – My Hall & Seat (Student)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('student');

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['team_id'];

$hallInfo = null;
$mySeat = null;
$allSeats = [];
$teammateIds = [];

if ($teamId) {
    // Get hall
    $stmt = $pdo->prepare("
        SELECT h.* FROM halls h
        JOIN hall_assignments ha ON h.id = ha.hall_id
        WHERE ha.team_id = ?
    ");
    $stmt->execute([$teamId]);
    $hallInfo = $stmt->fetch();

    // Get teammate IDs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE team_id = ? AND id != ?");
    $stmt->execute([$teamId, $userId]);
    $teammateIds = array_column($stmt->fetchAll(), 'id');

    if ($hallInfo) {
        // Get all seats for this hall
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name, u.team_id
            FROM seats s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.hall_id = ?
            ORDER BY s.seat_number
        ");
        $stmt->execute([$hallInfo['id']]);
        $allSeats = $stmt->fetchAll();

        // My seat
        $stmt = $pdo->prepare("SELECT seat_number FROM seats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $mySeat = $stmt->fetchColumn();
    }
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_student.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-building me-2"></i>My Hall & Seat</h4>
            </div>

            <?php if (!$hallInfo): ?>
            <div class="card content-card">
                <div class="card-body empty-state">
                    <i class="bi bi-building d-block"></i>
                    <p>No hall assigned yet. Contact the admin.</p>
                </div>
            </div>
            <?php else: ?>

            <!-- Hall Info Card -->
            <div class="card content-card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <i class="bi bi-building display-3 text-primary"></i>
                            <h4 class="mt-2"><?= sanitize($hallInfo['hall_name']) ?></h4>
                            <span class="badge bg-info">Capacity: <?= $hallInfo['capacity'] ?></span>
                        </div>
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <i class="bi bi-geo-alt-fill display-3 text-warning"></i>
                            <h4 class="mt-2"><?= $mySeat ? sanitize($mySeat) : 'Not Assigned' ?></h4>
                            <span class="badge bg-warning text-dark">My Seat</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <?php if ($hallInfo['location_url']): ?>
                            <i class="bi bi-map display-3 text-success"></i>
                            <h4 class="mt-2">Navigation</h4>
                            <a href="<?= sanitize($hallInfo['location_url']) ?>" target="_blank" class="btn btn-success">
                                <i class="bi bi-geo-alt-fill me-1"></i>Open Map
                            </a>
                            <?php else: ?>
                            <i class="bi bi-map display-3 text-muted"></i>
                            <h4 class="mt-2">No Map</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seating Layout -->
            <?php if (!empty($allSeats)): ?>
            <div class="card content-card">
                <div class="card-header"><i class="bi bi-grid-3x3 me-2"></i>Seating Layout</div>
                <div class="card-body">
                    <div class="d-flex gap-3 mb-3 flex-wrap">
                        <span><span class="d-inline-block rounded" style="width:16px;height:16px;background:#e9ecef;"></span> Empty</span>
                        <span><span class="d-inline-block rounded" style="width:16px;height:16px;background:#cfe2ff;"></span> Occupied</span>
                        <span><span class="d-inline-block rounded" style="width:16px;height:16px;background:var(--hc-primary);"></span> Your Seat</span>
                        <span><span class="d-inline-block rounded" style="width:16px;height:16px;background:#d1e7dd;"></span> Teammate</span>
                    </div>

                    <div class="seating-grid">
                        <?php foreach ($allSeats as $seat):
                            $class = 'seat-empty';
                            $tooltip = 'Empty';
                            if ($seat['user_id'] == $userId) {
                                $class = 'seat-self';
                                $tooltip = 'You (' . $seat['full_name'] . ')';
                            } elseif (in_array($seat['user_id'], $teammateIds)) {
                                $class = 'seat-teammate';
                                $tooltip = $seat['full_name'] . ' (Teammate)';
                            } elseif ($seat['user_id']) {
                                $class = 'seat-occupied';
                                $tooltip = $seat['full_name'];
                            }
                        ?>
                        <div class="seat-cell <?= $class ?>" data-bs-toggle="tooltip" title="<?= sanitize($tooltip) ?>">
                            <?= sanitize($seat['seat_number']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
