<?php
/**
 * HackCommand – Hall Overview (Coordinator)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('coordinator');

$halls = $pdo->query("SELECT * FROM halls ORDER BY hall_name")->fetchAll();
$selectedHall = isset($_GET['hall']) ? (int) $_GET['hall'] : ($halls[0]['id'] ?? 0);

$hallInfo = null;
$seats = [];
$teamColors = [];

if ($selectedHall) {
    $stmt = $pdo->prepare("SELECT * FROM halls WHERE id = ?");
    $stmt->execute([$selectedHall]);
    $hallInfo = $stmt->fetch();

    // Get all seats with user and team info
    $stmt = $pdo->prepare("
        SELECT s.*, u.full_name, u.team_id, t.team_name
        FROM seats s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN teams t ON u.team_id = t.id
        WHERE s.hall_id = ?
        ORDER BY s.seat_number
    ");
    $stmt->execute([$selectedHall]);
    $seats = $stmt->fetchAll();

    // Assign colors to teams
    $colors = ['#cfe2ff', '#d1e7dd', '#fff3cd', '#f8d7da', '#e2d9f3', '#d2e3fc', '#fce4ec', '#e8f5e9'];
    $colorIdx = 0;
    foreach ($seats as $seat) {
        if ($seat['team_id'] && !isset($teamColors[$seat['team_id']])) {
            $teamColors[$seat['team_id']] = $colors[$colorIdx % count($colors)];
            $colorIdx++;
        }
    }

    // Get teams assigned to this hall
    $stmt = $pdo->prepare("
        SELECT t.id, t.team_name, COUNT(u.id) as member_count
        FROM teams t
        JOIN hall_assignments ha ON t.id = ha.team_id AND ha.hall_id = ?
        LEFT JOIN users u ON u.team_id = t.id
        GROUP BY t.id, t.team_name
    ");
    $stmt->execute([$selectedHall]);
    $teamsInHall = $stmt->fetchAll();
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_coordinator.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-grid-3x3-gap-fill me-2"></i>Hall Overview</h4>
            </div>

            <!-- Hall Selector -->
            <div class="card content-card mb-4">
                <div class="card-body">
                    <form class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Select Hall</label>
                            <select name="hall" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($halls as $h): ?>
                                <option value="<?= $h['id'] ?>" <?= $selectedHall == $h['id'] ? 'selected' : '' ?>><?= sanitize($h['hall_name']) ?> (Cap: <?= $h['capacity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <?php if ($hallInfo && $hallInfo['location_url']): ?>
                            <a href="<?= sanitize($hallInfo['location_url']) ?>" target="_blank" class="btn btn-outline-info w-100">
                                <i class="bi bi-geo-alt-fill me-1"></i>View on Map
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($hallInfo): ?>
            <div class="row g-4">
                <!-- Teams in Hall -->
                <div class="col-lg-4">
                    <div class="card content-card">
                        <div class="card-header"><i class="bi bi-people-fill me-2"></i>Teams in Hall</div>
                        <div class="card-body">
                            <?php if (empty($teamsInHall)): ?>
                            <p class="text-muted">No teams assigned</p>
                            <?php else: ?>
                            <?php foreach ($teamsInHall as $t): ?>
                            <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: <?= $teamColors[$t['id']] ?? '#f8f9fa' ?>">
                                <span class="d-inline-block rounded me-2" style="width:14px;height:14px;background:<?= $teamColors[$t['id']] ?? '#ccc' ?>;border:1px solid #aaa"></span>
                                <strong><?= sanitize($t['team_name']) ?></strong>
                                <span class="badge bg-secondary ms-auto"><?= $t['member_count'] ?> members</span>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Seating Grid -->
                <div class="col-lg-8">
                    <div class="card content-card">
                        <div class="card-header"><i class="bi bi-grid-3x3 me-2"></i>Seating Layout</div>
                        <div class="card-body">
                            <?php if (empty($seats)): ?>
                            <div class="empty-state"><i class="bi bi-grid-3x3-gap d-block"></i><p>No seats generated</p></div>
                            <?php else: ?>
                            <div class="seating-grid">
                                <?php foreach ($seats as $seat):
                                    $bgColor = $seat['team_id'] ? ($teamColors[$seat['team_id']] ?? '#cfe2ff') : '#e9ecef';
                                ?>
                                <div class="seat-cell" style="background-color: <?= $bgColor ?>; color: #333; border: 2px solid rgba(0,0,0,0.1);"
                                     data-bs-toggle="tooltip" title="<?= $seat['full_name'] ? sanitize($seat['full_name']) . ' (' . sanitize($seat['team_name']) . ')' : 'Empty' ?>">
                                    <?= sanitize($seat['seat_number']) ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
