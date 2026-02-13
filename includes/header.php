<?php
/**
 * HackCommand – Header Include
 * Bootstrap 5 + custom styles + top navbar
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = $_SESSION['full_name'] ?? 'Guest';
$currentRole = $_SESSION['role'] ?? '';
$userId      = $_SESSION['user_id'] ?? 0;
$unreadCount = $userId ? getUnreadCount($pdo, $userId) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackCommand – Hackathon Operations</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animations CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top hc-navbar">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/">
            <i class="bi bi-terminal-fill me-2"></i>
            <strong>HackCommand</strong>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if ($userId): ?>
                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="<?= BASE_URL ?>/<?= $currentRole ?>/announcements.php">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notifBadge">
                            <?= $unreadCount ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= sanitize($currentUser) ?>
                        <span class="badge bg-info ms-1"><?= ucfirst(sanitize($currentRole)) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/<?= $currentRole ?>/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/auth/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
