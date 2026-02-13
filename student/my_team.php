<?php
/**
 * HackCommand – My Team (Student)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('student');

$teamId = $_SESSION['team_id'];
$team = null;
$members = [];

if ($teamId) {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, s.seat_number, h.hall_name
        FROM users u
        LEFT JOIN seats s ON s.user_id = u.id
        LEFT JOIN halls h ON s.hall_id = h.id
        WHERE u.team_id = ?
        ORDER BY u.full_name
    ");
    $stmt->execute([$teamId]);
    $members = $stmt->fetchAll();
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_student.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-people-fill me-2"></i>My Team</h4>
            </div>

            <?php if (!$team): ?>
            <div class="card content-card">
                <div class="card-body empty-state">
                    <i class="bi bi-people d-block"></i>
                    <p>You are not assigned to any team yet. Contact the admin.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-flag-fill me-2 text-primary"></i><?= sanitize($team['team_name']) ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3"><i class="bi bi-people-fill me-1"></i><?= count($members) ?> members</p>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>Name</th><th>Email</th><th>Hall</th><th>Seat</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($members as $i => $m): ?>
                            <tr class="<?= $m['id'] == $_SESSION['user_id'] ? 'table-primary' : '' ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= sanitize($m['full_name']) ?></strong>
                                    <?= $m['id'] == $_SESSION['user_id'] ? '<span class="badge bg-primary ms-1">You</span>' : '' ?>
                                </td>
                                <td><?= sanitize($m['email']) ?></td>
                                <td><?= $m['hall_name'] ? sanitize($m['hall_name']) : '—' ?></td>
                                <td><?= $m['seat_number'] ? sanitize($m['seat_number']) : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
