<?php
/**
 * HackCommand – Manage Halls (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$csrf = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name     = trim($_POST['hall_name'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 30);
        $locUrl   = trim($_POST['location_url'] ?? '');

        if ($name && $capacity > 0) {
            $stmt = $pdo->prepare("INSERT INTO halls (hall_name, capacity, location_url) VALUES (?, ?, ?)");
            $stmt->execute([$name, $capacity, $locUrl ?: null]);
            setFlash('success', 'Hall added successfully.');
        }
    } elseif ($action === 'edit') {
        $hallId   = (int) ($_POST['hall_id'] ?? 0);
        $name     = trim($_POST['hall_name'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 30);
        $locUrl   = trim($_POST['location_url'] ?? '');

        if ($hallId && $name) {
            $stmt = $pdo->prepare("UPDATE halls SET hall_name = ?, capacity = ?, location_url = ? WHERE id = ?");
            $stmt->execute([$name, $capacity, $locUrl ?: null, $hallId]);
            setFlash('success', 'Hall updated.');
        }
    } elseif ($action === 'delete') {
        $hallId = (int) ($_POST['hall_id'] ?? 0);
        if ($hallId) {
            $pdo->prepare("DELETE FROM halls WHERE id = ?")->execute([$hallId]);
            setFlash('success', 'Hall deleted.');
        }
    }
    redirect(BASE_URL . '/admin/manage_halls.php');
}

// Fetch halls with assignment and seat counts
$halls = $pdo->query("
    SELECT h.*,
        (SELECT COUNT(*) FROM seats WHERE hall_id = h.id) as total_seats,
        (SELECT COUNT(*) FROM seats WHERE hall_id = h.id AND user_id IS NOT NULL) as occupied_seats,
        (SELECT COUNT(*) FROM hall_assignments WHERE hall_id = h.id) as team_count
    FROM halls h ORDER BY h.hall_name
")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-building me-2"></i>Manage Halls</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHallModal">
                    <i class="bi bi-plus-circle-fill me-1"></i>Add Hall
                </button>
            </div>

            <?= showFlash() ?>

            <div class="row g-4">
                <?php foreach ($halls as $hall): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong><i class="bi bi-building me-1"></i><?= sanitize($hall['hall_name']) ?></strong>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editHallModal"
                                    data-id="<?= $hall['id'] ?>" data-name="<?= sanitize($hall['hall_name']) ?>"
                                    data-capacity="<?= $hall['capacity'] ?>" data-url="<?= sanitize($hall['location_url'] ?? '') ?>">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="hall_id" value="<?= $hall['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="fw-bold fs-5"><?= $hall['capacity'] ?></div>
                                    <small class="text-muted">Capacity</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold fs-5"><?= $hall['team_count'] ?></div>
                                    <small class="text-muted">Teams</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold fs-5"><?= $hall['occupied_seats'] ?>/<?= $hall['total_seats'] ?></div>
                                    <small class="text-muted">Seats</small>
                                </div>
                            </div>

                            <?php if ($hall['total_seats'] > 0): ?>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: <?= $hall['total_seats'] > 0 ? round(($hall['occupied_seats']/$hall['total_seats'])*100) : 0 ?>%"></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($hall['location_url']): ?>
                            <a href="<?= sanitize($hall['location_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                <i class="bi bi-geo-alt-fill me-1"></i>View on Map
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($halls)): ?>
                <div class="col-12">
                    <div class="empty-state"><i class="bi bi-building d-block"></i><p>No halls created yet</p></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Hall Modal -->
<div class="modal fade" id="addHallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2"></i>Add Hall</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label class="form-label">Hall Name</label><input type="text" name="hall_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="30" min="1" required></div>
                    <div class="mb-3"><label class="form-label">Google Maps URL <small class="text-muted">(optional)</small></label><input type="url" name="location_url" class="form-control" placeholder="https://maps.google.com/..."></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Hall</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Hall Modal -->
<div class="modal fade" id="editHallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Hall</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="hall_id" id="editHallId">
                    <div class="mb-3"><label class="form-label">Hall Name</label><input type="text" name="hall_name" id="editHallName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Capacity</label><input type="number" name="capacity" id="editHallCap" class="form-control" min="1" required></div>
                    <div class="mb-3"><label class="form-label">Google Maps URL</label><input type="url" name="location_url" id="editHallUrl" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editHallModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editHallId').value = btn.dataset.id;
    document.getElementById('editHallName').value = btn.dataset.name;
    document.getElementById('editHallCap').value = btn.dataset.capacity;
    document.getElementById('editHallUrl').value = btn.dataset.url;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
