<?php
/**
 * HackCommand – Attendance (Coordinator)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('coordinator');

$csrf = generateCsrfToken();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $hallId   = (int) ($_POST['hall_id'] ?? 0);
    $date     = $_POST['date'] ?? date('Y-m-d');
    $students = $_POST['attendance'] ?? [];

    if ($hallId && $date) {
        foreach ($students as $userId => $status) {
            $userId = (int) $userId;
            $status = in_array($status, ['present', 'absent']) ? $status : 'absent';

            // Upsert attendance
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND hall_id = ? AND date = ?");
            $stmt->execute([$userId, $hallId, $date]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE attendance SET status = ?, marked_by = ? WHERE user_id = ? AND hall_id = ? AND date = ?")
                    ->execute([$status, $_SESSION['user_id'], $userId, $hallId, $date]);
            } else {
                $pdo->prepare("INSERT INTO attendance (user_id, hall_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$userId, $hallId, $date, $status, $_SESSION['user_id']]);
            }
        }
        setFlash('success', 'Attendance saved successfully for ' . date('M d, Y', strtotime($date)) . '.');
        redirect(BASE_URL . '/coordinator/attendance.php?hall=' . $hallId . '&date=' . $date);
    }
}

$halls = $pdo->query("SELECT * FROM halls ORDER BY hall_name")->fetchAll();
$selectedHall = isset($_GET['hall']) ? (int) $_GET['hall'] : ($halls[0]['id'] ?? 0);
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get students for selected hall
$students = [];
if ($selectedHall) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, t.team_name, s.seat_number
        FROM users u
        JOIN roles r ON u.role_id = r.id
        JOIN teams t ON u.team_id = t.id
        JOIN hall_assignments ha ON t.id = ha.team_id AND ha.hall_id = ?
        LEFT JOIN seats s ON s.user_id = u.id AND s.hall_id = ?
        WHERE r.role_name = 'student'
        ORDER BY t.team_name, u.full_name
    ");
    $stmt->execute([$selectedHall, $selectedHall]);
    $students = $stmt->fetchAll();

    // Get existing attendance for this date
    $stmt = $pdo->prepare("SELECT user_id, status FROM attendance WHERE hall_id = ? AND date = ?");
    $stmt->execute([$selectedHall, $selectedDate]);
    $existingAttendance = [];
    while ($row = $stmt->fetch()) {
        $existingAttendance[$row['user_id']] = $row['status'];
    }
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_coordinator.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-check2-square me-2"></i>Attendance Management</h4>
            </div>

            <?= showFlash() ?>

            <!-- Filters -->
            <div class="card content-card mb-4">
                <div class="card-body">
                    <form class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Select Hall</label>
                            <select name="hall" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($halls as $h): ?>
                                <option value="<?= $h['id'] ?>" <?= $selectedHall == $h['id'] ? 'selected' : '' ?>><?= sanitize($h['hall_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?= $selectedDate ?>" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i>Load</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attendance Table -->
            <?php if (!empty($students)): ?>
            <div class="card content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-check me-2"></i>Mark Attendance – <?= date('M d, Y', strtotime($selectedDate)) ?></span>
                    <span class="badge bg-info"><?= count($students) ?> students</span>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="hall_id" value="<?= $selectedHall ?>">
                        <input type="hidden" name="date" value="<?= $selectedDate ?>">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>#</th><th>Student</th><th>Team</th><th>Seat</th><th class="text-center">Status</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($students as $i => $s):
                                    $currentStatus = $existingAttendance[$s['id']] ?? '';
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= sanitize($s['full_name']) ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= sanitize($s['team_name']) ?></span></td>
                                    <td><?= $s['seat_number'] ? sanitize($s['seat_number']) : '—' ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="attendance[<?= $s['id'] ?>]" value="present"
                                                   id="present_<?= $s['id'] ?>" <?= $currentStatus === 'present' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success" for="present_<?= $s['id'] ?>"><i class="bi bi-check-lg"></i> Present</label>

                                            <input type="radio" class="btn-check" name="attendance[<?= $s['id'] ?>]" value="absent"
                                                   id="absent_<?= $s['id'] ?>" <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger" for="absent_<?= $s['id'] ?>"><i class="bi bi-x-lg"></i> Absent</label>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="setAll('present')">
                                <i class="bi bi-check-all me-1"></i>Mark All Present
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAll('absent')">
                                <i class="bi bi-x-circle me-1"></i>Mark All Absent
                            </button>
                            <div class="ms-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save-fill me-1"></i>Save Attendance
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php elseif ($selectedHall): ?>
            <div class="card content-card">
                <div class="card-body empty-state">
                    <i class="bi bi-people d-block"></i>
                    <p>No students assigned to this hall yet.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function setAll(status) {
    document.querySelectorAll('input[value="' + status + '"]').forEach(function(el) {
        el.checked = true;
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
