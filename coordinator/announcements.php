<?php
/**
 * HackCommand – Announcements (Coordinator)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('coordinator');

$csrf = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title   = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($title && $message) {
            $stmt = $pdo->prepare("INSERT INTO announcements (title, message, posted_by) VALUES (?, ?, ?)");
            $stmt->execute([$title, $message, $_SESSION['user_id']]);
            setFlash('success', 'Announcement posted.');
        }
    }
    redirect(BASE_URL . '/coordinator/announcements.php');
}

$announcements = $pdo->query("
    SELECT a.*, u.full_name
    FROM announcements a
    JOIN users u ON a.posted_by = u.id
    ORDER BY a.created_at DESC
")->fetchAll();

// Mark all as read
foreach ($announcements as $ann) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)");
    $stmt->execute([$ann['id'], $_SESSION['user_id']]);
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_coordinator.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-megaphone-fill me-2"></i>Announcements</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnModal">
                    <i class="bi bi-plus-circle-fill me-1"></i>Post Announcement
                </button>
            </div>

            <?= showFlash() ?>

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
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Post Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Post</button></div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
