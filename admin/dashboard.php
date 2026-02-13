<?php
/**
 * HackCommand – Admin Dashboard
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

// Aggregate statistics
$stats = [];

// Total participants (students)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name = 'student'");
$stmt->execute();
$stats['participants'] = $stmt->fetchColumn();

// Total teams
$stmt = $pdo->query("SELECT COUNT(*) FROM teams");
$stats['teams'] = $stmt->fetchColumn();

// Total halls
$stmt = $pdo->query("SELECT COUNT(*) FROM halls");
$stats['halls'] = $stmt->fetchColumn();

// Total submissions
$stmt = $pdo->query("SELECT COUNT(*) FROM submissions");
$stats['submissions'] = $stmt->fetchColumn();

// Average score
$stmt = $pdo->query("SELECT COALESCE(AVG(total), 0) FROM scores");
$stats['avg_score'] = round($stmt->fetchColumn(), 1);

// Today's attendance
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'present'");
$stmt->execute([$today]);
$stats['present_today'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$stmt->execute([$today]);
$stats['total_attendance'] = $stmt->fetchColumn();
$stats['attendance_pct'] = $stats['total_attendance'] > 0
    ? round(($stats['present_today'] / $stats['total_attendance']) * 100)
    : 0;

// Recent announcements
$stmt = $pdo->query("SELECT a.*, u.full_name FROM announcements a JOIN users u ON a.posted_by = u.id ORDER BY a.created_at DESC LIMIT 5");
$recentAnnouncements = $stmt->fetchAll();

// Top 5 teams by score
$stmt = $pdo->query("
    SELECT t.team_name, COALESCE(AVG(s.total), 0) as avg_total
    FROM teams t
    LEFT JOIN scores s ON t.id = s.team_id
    GROUP BY t.id, t.team_name
    ORDER BY avg_total DESC
    LIMIT 5
");
$topTeams = $stmt->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h4>
                <span class="text-muted"><?= date('l, F j, Y') ?></span>
            </div>

            <?= showFlash() ?>

            <!-- Stats Cards Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card bg-gradient-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill stat-icon"></i>
                            <div class="stat-value"><?= $stats['participants'] ?></div>
                            <div class="stat-label">Participants</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card bg-gradient-success">
                        <div class="card-body text-center">
                            <i class="bi bi-person-lines-fill stat-icon"></i>
                            <div class="stat-value"><?= $stats['teams'] ?></div>
                            <div class="stat-label">Teams</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card bg-gradient-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-building stat-icon"></i>
                            <div class="stat-value"><?= $stats['halls'] ?></div>
                            <div class="stat-label">Halls</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card bg-gradient-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-cloud-upload-fill stat-icon"></i>
                            <div class="stat-value"><?= $stats['submissions'] ?></div>
                            <div class="stat-label">Submissions</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card bg-gradient-info">
                        <div class="card-body text-center">
                            <i class="bi bi-star-fill stat-icon"></i>
                            <div class="stat-value"><?= $stats['avg_score'] ?></div>
                            <div class="stat-label">Avg Score</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card bg-gradient-purple">
                        <div class="card-body text-center">
                            <i class="bi bi-check2-square stat-icon"></i>
                            <div class="stat-value"><?= $stats['attendance_pct'] ?>%</div>
                            <div class="stat-label">Attendance</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Recent Announcements -->
                <div class="col-lg-7">
                    <div class="card content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-megaphone-fill me-2"></i>Recent Announcements</span>
                            <a href="<?= BASE_URL ?>/admin/announcements.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentAnnouncements)): ?>
                            <div class="empty-state">
                                <i class="bi bi-megaphone d-block"></i>
                                <p>No announcements yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($recentAnnouncements as $ann): ?>
                            <div class="card announcement-card mb-2">
                                <div class="card-body py-2 px-3">
                                    <h6 class="mb-1"><?= sanitize($ann['title']) ?></h6>
                                    <p class="mb-1 small text-muted"><?= sanitize(substr($ann['message'], 0, 120)) ?>...</p>
                                    <span class="announcement-time">
                                        <i class="bi bi-person-fill me-1"></i><?= sanitize($ann['full_name']) ?> &middot;
                                        <i class="bi bi-clock me-1"></i><?= timeAgo($ann['created_at']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Teams -->
                <div class="col-lg-5">
                    <div class="card content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-trophy-fill me-2"></i>Top Teams</span>
                            <a href="<?= BASE_URL ?>/admin/leaderboard.php" class="btn btn-sm btn-outline-primary">Leaderboard</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($topTeams)): ?>
                            <div class="empty-state">
                                <i class="bi bi-trophy d-block"></i>
                                <p>No scores yet</p>
                            </div>
                            <?php else: ?>
                            <table class="table table-sm mb-0">
                                <thead><tr><th>#</th><th>Team</th><th class="text-end">Avg Score</th></tr></thead>
                                <tbody>
                                <?php foreach ($topTeams as $i => $t): ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge <?= $i < 3 ? 'rank-'.($i+1) : 'rank-other' ?>"><?= $i + 1 ?></span>
                                    </td>
                                    <td><?= sanitize($t['team_name']) ?></td>
                                    <td class="text-end"><strong><?= number_format($t['avg_total'], 1) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
