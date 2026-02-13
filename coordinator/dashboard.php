<?php
/**
 * HackCommand – Coordinator Dashboard
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('coordinator');

// Hall stats
$hallCount = $pdo->query("SELECT COUNT(*) FROM halls")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name = 'student'")->fetchColumn();

// Today's attendance summary
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'present'");
$stmt->execute([$today]);
$presentToday = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$stmt->execute([$today]);
$totalMarked = $stmt->fetchColumn();

$attendancePct = $totalMarked > 0 ? round(($presentToday / $totalMarked) * 100) : 0;

// Attendance by hall today
$hallAttendance = $pdo->prepare("
    SELECT h.hall_name,
           COUNT(CASE WHEN att.status = 'present' THEN 1 END) as present_count,
           COUNT(att.id) as total_marked
    FROM halls h
    LEFT JOIN attendance att ON h.id = att.hall_id AND att.date = ?
    GROUP BY h.id, h.hall_name
    ORDER BY h.hall_name
");
$hallAttendance->execute([$today]);
$hallStats = $hallAttendance->fetchAll();

// Recent announcements
$recentAnn = $pdo->query("SELECT a.*, u.full_name FROM announcements a JOIN users u ON a.posted_by = u.id ORDER BY a.created_at DESC LIMIT 5")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_coordinator.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-speedometer2 me-2"></i>Coordinator Dashboard</h4>
                <small class="text-muted"><?= date('l, F j, Y') ?></small>
            </div>

            <?= showFlash() ?>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-building stat-icon"></i>
                            <div class="stat-value"><?= $hallCount ?></div>
                            <div class="stat-label">Halls</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-success">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill stat-icon"></i>
                            <div class="stat-value"><?= $totalStudents ?></div>
                            <div class="stat-label">Students</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-check2-square stat-icon"></i>
                            <div class="stat-value"><?= $presentToday ?></div>
                            <div class="stat-label">Present Today</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-purple">
                        <div class="card-body text-center">
                            <i class="bi bi-percent stat-icon"></i>
                            <div class="stat-value"><?= $attendancePct ?>%</div>
                            <div class="stat-label">Attendance Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Hall-wise Attendance -->
                <div class="col-lg-6">
                    <div class="card content-card">
                        <div class="card-header"><i class="bi bi-bar-chart-fill me-2"></i>Today's Attendance by Hall</div>
                        <div class="card-body">
                            <?php if (empty($hallStats)): ?>
                            <div class="empty-state"><i class="bi bi-building d-block"></i><p>No halls</p></div>
                            <?php else: ?>
                            <?php foreach ($hallStats as $hs): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong><?= sanitize($hs['hall_name']) ?></strong>
                                    <span><?= $hs['present_count'] ?> / <?= $hs['total_marked'] ?></span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: <?= $hs['total_marked'] > 0 ? round(($hs['present_count']/$hs['total_marked'])*100) : 0 ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="col-lg-6">
                    <div class="card content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-megaphone-fill me-2"></i>Recent Announcements</span>
                            <a href="<?= BASE_URL ?>/coordinator/announcements.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentAnn)): ?>
                            <div class="empty-state"><i class="bi bi-megaphone d-block"></i><p>No announcements</p></div>
                            <?php else: ?>
                            <?php foreach ($recentAnn as $ann): ?>
                            <div class="card announcement-card mb-2">
                                <div class="card-body py-2 px-3">
                                    <h6 class="mb-1"><?= sanitize($ann['title']) ?></h6>
                                    <p class="mb-1 small text-muted"><?= sanitize(substr($ann['message'], 0, 80)) ?>...</p>
                                    <span class="announcement-time"><i class="bi bi-clock me-1"></i><?= timeAgo($ann['created_at']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
