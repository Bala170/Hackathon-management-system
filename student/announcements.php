<?php
/**
 * HackCommand – Student Announcements
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('student');

$userId = $_SESSION['user_id'];

$announcements = $pdo->query("
    SELECT a.*, u.full_name
    FROM announcements a
    JOIN users u ON a.posted_by = u.id
    ORDER BY a.created_at DESC
")->fetchAll();

// Mark all as read
foreach ($announcements as $ann) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)");
    $stmt->execute([$ann['id'], $userId]);
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_student.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-megaphone-fill me-2"></i>Announcements</h4>
            </div>

            <?php if (empty($announcements)): ?>
            <div class="card content-card"><div class="card-body empty-state"><i class="bi bi-megaphone d-block"></i><p>No announcements yet</p></div></div>
            <?php else: ?>
            <?php foreach ($announcements as $ann): ?>
            <div class="card content-card announcement-card mb-3">
                <div class="card-body">
                    <h5 class="mb-2"><?= sanitize($ann['title']) ?></h5>
                    <p class="mb-2"><?= nl2br(sanitize($ann['message'])) ?></p>
                    <small class="text-muted">
                        <i class="bi bi-person-fill me-1"></i><?= sanitize($ann['full_name']) ?>
                        &middot; <i class="bi bi-clock me-1"></i><?= timeAgo($ann['created_at']) ?>
                        &middot; <?= date('M d, Y h:i A', strtotime($ann['created_at'])) ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
