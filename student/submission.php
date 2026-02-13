<?php
/**
 * HackCommand – Project Submission (Student)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('student');

$csrf = generateCsrfToken();
$teamId = $_SESSION['team_id'];

// Check existing submission
$existing = null;
if ($teamId) {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE team_id = ?");
    $stmt->execute([$teamId]);
    $existing = $stmt->fetch();
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken() && $teamId) {
    $title   = trim($_POST['title'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $fileUrl = trim($_POST['file_url'] ?? '');

    if ($title) {
        if ($existing) {
            // Update
            $stmt = $pdo->prepare("UPDATE submissions SET title = ?, description = ?, file_url = ? WHERE team_id = ?");
            $stmt->execute([$title, $desc, $fileUrl ?: null, $teamId]);
            setFlash('success', 'Submission updated successfully.');
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO submissions (team_id, title, description, file_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$teamId, $title, $desc, $fileUrl ?: null]);
            setFlash('success', 'Project submitted successfully!');
        }
        redirect(BASE_URL . '/student/submission.php');
    }
}
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_student.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-cloud-upload-fill me-2"></i>Project Submission</h4>
            </div>

            <?= showFlash() ?>

            <?php if (!$teamId): ?>
            <div class="card content-card">
                <div class="card-body empty-state">
                    <i class="bi bi-people d-block"></i>
                    <p>You must be in a team to submit a project.</p>
                </div>
            </div>
            <?php else: ?>

            <?php if ($existing): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-1"></i>
                Your team has already submitted. You can update the submission below.
            </div>
            <?php endif; ?>

            <div class="card content-card">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <div class="mb-3">
                            <label class="form-label">Project Title</label>
                            <input type="text" name="title" class="form-control" required
                                   value="<?= sanitize($existing['title'] ?? '') ?>"
                                   placeholder="Enter your project title">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="5"
                                      placeholder="Describe your project..."><?= sanitize($existing['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project URL / Repository Link</label>
                            <input type="url" name="file_url" class="form-control"
                                   value="<?= sanitize($existing['file_url'] ?? '') ?>"
                                   placeholder="https://github.com/your-team/project">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload-fill me-1"></i>
                            <?= $existing ? 'Update Submission' : 'Submit Project' ?>
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($existing): ?>
            <div class="card content-card mt-3">
                <div class="card-header"><i class="bi bi-check-circle-fill text-success me-2"></i>Current Submission</div>
                <div class="card-body">
                    <h5><?= sanitize($existing['title']) ?></h5>
                    <p><?= nl2br(sanitize($existing['description'] ?? '')) ?></p>
                    <?php if ($existing['file_url']): ?>
                    <a href="<?= sanitize($existing['file_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-link-45deg me-1"></i>View Project Link
                    </a>
                    <?php endif; ?>
                    <p class="text-muted mt-2 mb-0"><small>Submitted: <?= timeAgo($existing['submitted_at']) ?></small></p>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
