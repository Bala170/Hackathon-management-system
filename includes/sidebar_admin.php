<!-- Admin Sidebar -->
<nav class="sidebar bg-dark text-white" id="sidebarAdmin">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <h6 class="mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</h6>
    </div>
    <ul class="nav flex-column p-2">
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/manage_users.php">
                <i class="bi bi-people-fill me-2"></i>Manage Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'manage_teams.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/manage_teams.php">
                <i class="bi bi-person-lines-fill me-2"></i>Manage Teams
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'manage_halls.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/manage_halls.php">
                <i class="bi bi-building me-2"></i>Manage Halls
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'assign_halls.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/assign_halls.php">
                <i class="bi bi-signpost-split me-2"></i>Assign Halls
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'manage_seats.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/manage_seats.php">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>Seating
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'announcements.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/announcements.php">
                <i class="bi bi-megaphone-fill me-2"></i>Announcements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'submissions.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/submissions.php">
                <i class="bi bi-cloud-upload-fill me-2"></i>Submissions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'scoring.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/scoring.php">
                <i class="bi bi-star-fill me-2"></i>Scoring
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'leaderboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/leaderboard.php">
                <i class="bi bi-trophy-fill me-2"></i>Leaderboard
            </a>
        </li>
    </ul>
</nav>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMobile">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column p-2">
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/manage_users.php"><i class="bi bi-people-fill me-2"></i>Manage Users</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/manage_teams.php"><i class="bi bi-person-lines-fill me-2"></i>Manage Teams</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/manage_halls.php"><i class="bi bi-building me-2"></i>Manage Halls</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/assign_halls.php"><i class="bi bi-signpost-split me-2"></i>Assign Halls</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/manage_seats.php"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Seating</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/announcements.php"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/submissions.php"><i class="bi bi-cloud-upload-fill me-2"></i>Submissions</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/scoring.php"><i class="bi bi-star-fill me-2"></i>Scoring</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/leaderboard.php"><i class="bi bi-trophy-fill me-2"></i>Leaderboard</a></li>
        </ul>
    </div>
</div>
