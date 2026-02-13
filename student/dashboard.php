<?php
/**
 * HackCommand – Student Dashboard
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('student');

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['team_id'];

// Get team info
$team = null;
if ($teamId) {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch();
}

// Hall assignment
$hallInfo = null;
$mySeat = null;
if ($teamId) {
    $stmt = $pdo->prepare("
        SELECT h.* FROM halls h
        JOIN hall_assignments ha ON h.id = ha.hall_id
        WHERE ha.team_id = ?
    ");
    $stmt->execute([$teamId]);
    $hallInfo = $stmt->fetch();

    // My seat
    $stmt = $pdo->prepare("SELECT seat_number FROM seats WHERE user_id = ?");
    $stmt->execute([$userId]);
    $mySeat = $stmt->fetchColumn();
}

// Today's attendance
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT status FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $today]);
$attendanceStatus = $stmt->fetchColumn();

// Latest announcements
$stmt = $pdo->query("SELECT a.*, u.full_name FROM announcements a JOIN users u ON a.posted_by = u.id ORDER BY a.created_at DESC LIMIT 5");
$latestAnnouncements = $stmt->fetchAll();

// Team leaderboard position
$leaderboardPos = '—';
if ($teamId) {
    $stmt = $pdo->query("
        SELECT t.id, COALESCE(SUM(s.total), 0) as total_score
        FROM teams t LEFT JOIN scores s ON t.id = s.team_id
        GROUP BY t.id ORDER BY total_score DESC
    ");
    $allTeams = $stmt->fetchAll();
    foreach ($allTeams as $i => $t) {
        if ($t['id'] == $teamId) {
            $leaderboardPos = '#' . ($i + 1);
            break;
        }
    }
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_student.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-speedometer2 me-2"></i>Welcome, <?= sanitize($_SESSION['full_name']) ?>!</h4>
                <small class="text-muted"><?= date('l, F j, Y') ?></small>
            </div>

            <?= showFlash() ?>

            <!-- Quick Info Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill stat-icon"></i>
                            <div class="stat-value"><?= $team ? sanitize($team['team_name']) : '—' ?></div>
                            <div class="stat-label">My Team</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-success">
                        <div class="card-body text-center">
                            <i class="bi bi-building stat-icon"></i>
                            <div class="stat-value"><?= $hallInfo ? sanitize($hallInfo['hall_name']) : '—' ?></div>
                            <div class="stat-label">My Hall</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-geo-alt-fill stat-icon"></i>
                            <div class="stat-value"><?= $mySeat ? sanitize($mySeat) : '—' ?></div>
                            <div class="stat-label">My Seat</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-info">
                        <div class="card-body text-center">
                            <i class="bi bi-trophy-fill stat-icon"></i>
                            <div class="stat-value"><?= $leaderboardPos ?></div>
                            <div class="stat-label">Leaderboard</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Announcements -->
                <div class="col-lg-7">
                    <div class="card content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-megaphone-fill me-2"></i>Announcements</span>
                            <a href="<?= BASE_URL ?>/student/announcements.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($latestAnnouncements)): ?>
                            <div class="empty-state"><i class="bi bi-megaphone d-block"></i><p>No announcements yet</p></div>
                            <?php else: ?>
                            <?php foreach ($latestAnnouncements as $ann):
                                $stmt = $pdo->prepare("SELECT id FROM announcement_reads WHERE announcement_id = ? AND user_id = ?");
                                $stmt->execute([$ann['id'], $userId]);
                                $isRead = (bool) $stmt->fetch();
                            ?>
                            <div class="card announcement-card <?= !$isRead ? 'unread' : '' ?> mb-2">
                                <div class="card-body py-2 px-3">
                                    <h6 class="mb-1"><?= sanitize($ann['title']) ?>
                                        <?php if (!$isRead): ?><span class="badge bg-danger ms-1">New</span><?php endif; ?>
                                    </h6>
                                    <p class="mb-1 small text-muted"><?= sanitize(substr($ann['message'], 0, 100)) ?>...</p>
                                    <span class="announcement-time"><i class="bi bi-clock me-1"></i><?= timeAgo($ann['created_at']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-5">
                    <!-- Hall Info -->
                    <?php if ($hallInfo): ?>
                    <div class="card content-card mb-3">
                        <div class="card-header"><i class="bi bi-building me-2"></i>Hall Information</div>
                        <div class="card-body">
                            <p><strong>Hall:</strong> <?= sanitize($hallInfo['hall_name']) ?></p>
                            <p><strong>Seat:</strong> <?= $mySeat ? sanitize($mySeat) : 'Not assigned' ?></p>
                            <?php if ($hallInfo['location_url']): ?>
                            <a href="<?= sanitize($hallInfo['location_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info w-100">
                                <i class="bi bi-geo-alt-fill me-1"></i>Navigate to Hall
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Attendance Status -->
                    <div class="card content-card mb-3">
                        <div class="card-header"><i class="bi bi-check2-square me-2"></i>Today's Attendance</div>
                        <div class="card-body text-center">
                            <?php if ($attendanceStatus === 'present'): ?>
                            <span class="badge bg-success fs-5 p-2"><i class="bi bi-check-circle-fill me-1"></i>Present</span>
                            <?php elseif ($attendanceStatus === 'absent'): ?>
                            <span class="badge bg-danger fs-5 p-2"><i class="bi bi-x-circle-fill me-1"></i>Absent</span>
                            <?php else: ?>
                            <span class="badge bg-secondary fs-5 p-2"><i class="bi bi-clock me-1"></i>Not Marked</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
