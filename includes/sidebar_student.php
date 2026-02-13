<!-- Student Sidebar -->
<nav class="sidebar bg-dark text-white" id="sidebarStudent">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <h6 class="mb-0"><i class="bi bi-mortarboard-fill me-2"></i>Student Panel</h6>
    </div>
    <ul class="nav flex-column p-2">
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/student/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'announcements.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/student/announcements.php">
                <i class="bi bi-megaphone-fill me-2"></i>Announcements
                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'my_team.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/student/my_team.php">
                <i class="bi bi-people-fill me-2"></i>My Team
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'my_hall.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/student/my_hall.php">
                <i class="bi bi-building me-2"></i>My Hall & Seat
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'submission.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/student/submission.php">
                <i class="bi bi-cloud-upload-fill me-2"></i>Submission
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'leaderboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/student/leaderboard.php">
                <i class="bi bi-trophy-fill me-2"></i>Leaderboard
            </a>
        </li>
    </ul>
</nav>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMobile">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="bi bi-mortarboard-fill me-2"></i>Student Panel</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column p-2">
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/student/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/student/announcements.php"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/student/my_team.php"><i class="bi bi-people-fill me-2"></i>My Team</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/student/my_hall.php"><i class="bi bi-building me-2"></i>My Hall & Seat</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/student/submission.php"><i class="bi bi-cloud-upload-fill me-2"></i>Submission</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/student/leaderboard.php"><i class="bi bi-trophy-fill me-2"></i>Leaderboard</a></li>
        </ul>
    </div>
</div>
