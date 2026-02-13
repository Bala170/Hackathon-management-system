<!-- Coordinator Sidebar -->
<nav class="sidebar bg-dark text-white" id="sidebarCoordinator">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <h6 class="mb-0"><i class="bi bi-clipboard2-check-fill me-2"></i>Coordinator Panel</h6>
    </div>
    <ul class="nav flex-column p-2">
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/coordinator/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/coordinator/attendance.php">
                <i class="bi bi-check2-square me-2"></i>Attendance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'announcements.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/coordinator/announcements.php">
                <i class="bi bi-megaphone-fill me-2"></i>Announcements
                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) === 'hall_overview.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/coordinator/hall_overview.php">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>Hall Overview
            </a>
        </li>
    </ul>
</nav>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMobile">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="bi bi-clipboard2-check-fill me-2"></i>Coordinator Panel</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column p-2">
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/coordinator/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/coordinator/attendance.php"><i class="bi bi-check2-square me-2"></i>Attendance</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/coordinator/announcements.php"><i class="bi bi-megaphone-fill me-2"></i>Announcements</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/coordinator/hall_overview.php"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Hall Overview</a></li>
        </ul>
    </div>
</div>
