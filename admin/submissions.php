<?php
/**
 * HackCommand – Submissions (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$submissions = $pdo->query("
    SELECT sub.*, t.team_name
    FROM submissions sub
    JOIN teams t ON sub.team_id = t.id
    ORDER BY sub.submitted_at DESC
")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-cloud-upload-fill me-2"></i>Project Submissions</h4>
            </div>

            <?= showFlash() ?>

            <div class="card content-card">
                <div class="card-body">
                    <?php if (empty($submissions)): ?>
                    <div class="empty-state"><i class="bi bi-cloud-upload d-block"></i><p>No submissions yet</p></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>Team</th><th>Project Title</th><th>Description</th><th>Link/File</th><th>Submitted</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($submissions as $i => $sub): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= sanitize($sub['team_name']) ?></strong></td>
                                <td><?= sanitize($sub['title']) ?></td>
                                <td class="small"><?= sanitize(substr($sub['description'] ?? '', 0, 100)) ?></td>
                                <td>
                                    <?php if ($sub['file_url']): ?>
                                    <a href="<?= sanitize($sub['file_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg me-1"></i>View
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= timeAgo($sub['submitted_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
