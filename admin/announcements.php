<?php
/**
 * HackCommand – Announcements (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

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
    } elseif ($action === 'edit') {
        $annId   = (int) ($_POST['ann_id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($annId && $title && $message) {
            $stmt = $pdo->prepare("UPDATE announcements SET title = ?, message = ? WHERE id = ?");
            $stmt->execute([$title, $message, $annId]);
            setFlash('success', 'Announcement updated.');
        }
    } elseif ($action === 'delete') {
        $annId = (int) ($_POST['ann_id'] ?? 0);
        if ($annId) {
            $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$annId]);
            setFlash('success', 'Announcement deleted.');
        }
    }
    redirect(BASE_URL . '/admin/announcements.php');
}

$announcements = $pdo->query("
    SELECT a.*, u.full_name
    FROM announcements a
    JOIN users u ON a.posted_by = u.id
    ORDER BY a.created_at DESC
")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-megaphone-fill me-2"></i>Announcements</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnModal">
                    <i class="bi bi-plus-circle-fill me-1"></i>New Announcement
                </button>
            </div>

            <?= showFlash() ?>

            <?php if (empty($announcements)): ?>
            <div class="card content-card"><div class="card-body empty-state"><i class="bi bi-megaphone d-block"></i><p>No announcements yet</p></div></div>
            <?php else: ?>
            <?php foreach ($announcements as $ann): ?>
            <div class="card content-card announcement-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1"><?= sanitize($ann['title']) ?></h5>
                            <p class="mb-2"><?= nl2br(sanitize($ann['message'])) ?></p>
                            <small class="text-muted">
                                <i class="bi bi-person-fill me-1"></i><?= sanitize($ann['full_name']) ?>
                                &middot; <i class="bi bi-clock me-1"></i><?= timeAgo($ann['created_at']) ?>
                            </small>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAnnModal"
                                data-id="<?= $ann['id'] ?>" data-title="<?= sanitize($ann['title']) ?>"
                                data-message="<?= sanitize($ann['message']) ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ann_id" value="<?= $ann['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </div>
                    </div>
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
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>New Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Post Announcement</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Announcement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="ann_id" id="editAnnId">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" id="editAnnTitle" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea name="message" id="editAnnMsg" class="form-control" rows="4" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editAnnModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editAnnId').value = btn.dataset.id;
    document.getElementById('editAnnTitle').value = btn.dataset.title;
    document.getElementById('editAnnMsg').value = btn.dataset.message;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
