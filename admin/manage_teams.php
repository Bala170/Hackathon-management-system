<?php
/**
 * HackCommand – Manage Teams (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$csrf = generateCsrfToken();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $teamName = trim($_POST['team_name'] ?? '');
        $memberIds = $_POST['members'] ?? [];
        if ($teamName) {
            $stmt = $pdo->prepare("INSERT INTO teams (team_name) VALUES (?)");
            try {
                $stmt->execute([$teamName]);
                $newTeamId = $pdo->lastInsertId();
                
                // Assign selected members to the new team
                if (!empty($memberIds) && is_array($memberIds)) {
                    $updateStmt = $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ? AND team_id IS NULL");
                    foreach ($memberIds as $memberId) {
                        $updateStmt->execute([$newTeamId, (int)$memberId]);
                    }
                }
                
                $memberCount = count($memberIds);
                setFlash('success', "Team created successfully" . ($memberCount > 0 ? " with $memberCount member(s)." : "."));
            } catch (PDOException $e) {
                setFlash('danger', 'Team name already exists.');
            }
        }
    } elseif ($action === 'edit') {
        $teamId   = (int) ($_POST['team_id'] ?? 0);
        $teamName = trim($_POST['team_name'] ?? '');
        $newMemberIds = $_POST['new_members'] ?? [];
        if ($teamId && $teamName) {
            $stmt = $pdo->prepare("UPDATE teams SET team_name = ? WHERE id = ?");
            try {
                $stmt->execute([$teamName, $teamId]);
                
                // Add new members if selected
                $addedCount = 0;
                if (!empty($newMemberIds) && is_array($newMemberIds)) {
                    $updateStmt = $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ? AND team_id IS NULL");
                    foreach ($newMemberIds as $memberId) {
                        $updateStmt->execute([$teamId, (int)$memberId]);
                        $addedCount++;
                    }
                }
                
                setFlash('success', 'Team updated.' . ($addedCount > 0 ? " Added $addedCount new member(s)." : ''));
            } catch (PDOException $e) {
                setFlash('danger', 'Error updating team.');
            }
        }
    } elseif ($action === 'delete') {
        $teamId = (int) ($_POST['team_id'] ?? 0);
        if ($teamId) {
            // Remove team assignment from users first
            $pdo->prepare("UPDATE users SET team_id = NULL WHERE team_id = ?")->execute([$teamId]);
            $pdo->prepare("DELETE FROM teams WHERE id = ?")->execute([$teamId]);
            setFlash('success', 'Team deleted.');
        }
    } elseif ($action === 'assign_member') {
        $teamId = (int) ($_POST['team_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($teamId && $userId) {
            $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ?")->execute([$teamId, $userId]);
            setFlash('success', 'Member assigned to team.');
        }
    } elseif ($action === 'remove_member') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId) {
            $pdo->prepare("UPDATE users SET team_id = NULL WHERE id = ?")->execute([$userId]);
            setFlash('success', 'Member removed from team.');
        }
    }
    redirect(BASE_URL . '/admin/manage_teams.php');
}

// Fetch teams with member counts
$teams = $pdo->query("
    SELECT t.*, COUNT(u.id) as member_count
    FROM teams t
    LEFT JOIN users u ON u.team_id = t.id
    GROUP BY t.id
    ORDER BY t.team_name
")->fetchAll();

// Unassigned students
$unassigned = $pdo->query("
    SELECT u.id, u.full_name, u.email
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.role_name = 'student' AND u.team_id IS NULL
    ORDER BY u.full_name
")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-person-lines-fill me-2"></i>Manage Teams</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                    <i class="bi bi-plus-circle-fill me-1"></i>Create Team
                </button>
            </div>

            <?= showFlash() ?>

            <div class="row g-4">
                <?php foreach ($teams as $team): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong><?= sanitize($team['team_name']) ?></strong>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTeamModal"
                                    data-id="<?= $team['id'] ?>" data-name="<?= sanitize($team['team_name']) ?>">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-2"><i class="bi bi-people-fill me-1"></i><?= $team['member_count'] ?> members</p>

                            <?php
                            $members = $pdo->prepare("SELECT id, full_name, email FROM users WHERE team_id = ? ORDER BY full_name");
                            $members->execute([$team['id']]);
                            $memberList = $members->fetchAll();
                            ?>

                            <?php if (empty($memberList)): ?>
                            <p class="text-muted small">No members assigned</p>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($memberList as $m): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                                    <span class="small"><?= sanitize($m['full_name']) ?></span>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="action" value="remove_member">
                                        <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm text-danger p-0" title="Remove"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>

                            <!-- Add member -->
                            <?php if (!empty($unassigned)): ?>
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="action" value="assign_member">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <div class="input-group input-group-sm">
                                    <select name="user_id" class="form-select form-select-sm">
                                        <?php foreach ($unassigned as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= sanitize($u['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-plus"></i></button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($teams)): ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-person-lines-fill d-block"></i>
                        <p>No teams created yet</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Team Modal -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2"></i>Create Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="team_name" class="form-control" placeholder="Enter team name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Add Team Members</label>
                        <p class="text-muted small mb-2">Select students to add to this team (optional). Hold Ctrl/Cmd to select multiple.</p>
                        <?php if (!empty($unassigned)): ?>
                        <select name="members[]" class="form-select" multiple size="6" style="height: auto;">
                            <?php foreach ($unassigned as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= sanitize($u['full_name']) ?> (<?= sanitize($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i><?= count($unassigned) ?> unassigned student(s) available</div>
                        <?php else: ?>
                        <div class="alert alert-info py-2 mb-0">
                            <i class="bi bi-info-circle me-1"></i>No unassigned students available. You can add members after creating the team.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="team_id" id="editTeamId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="team_name" id="editTeamName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Members</label>
                        <div id="editTeamMembers" class="border rounded p-2" style="min-height: 60px; background: #f8f9fa;">
                            <span class="text-muted small">Loading...</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Add More Members</label>
                        <?php if (!empty($unassigned)): ?>
                        <select name="new_members[]" id="editNewMembers" class="form-select" multiple size="4" style="height: auto;">
                            <?php foreach ($unassigned as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= sanitize($u['full_name']) ?> (<?= sanitize($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <div class="alert alert-info py-2 mb-0 small">
                            <i class="bi bi-info-circle me-1"></i>No unassigned students available.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Store team members data for edit modal
const teamMembersData = <?= json_encode(array_reduce($teams, function($carry, $team) use ($pdo) {
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE team_id = ?");
    $stmt->execute([$team['id']]);
    $carry[$team['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $carry;
}, [])) ?>;

document.getElementById('editTeamModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const teamId = btn.dataset.id;
    document.getElementById('editTeamId').value = teamId;
    document.getElementById('editTeamName').value = btn.dataset.name;
    
    // Display current members
    const membersContainer = document.getElementById('editTeamMembers');
    const members = teamMembersData[teamId] || [];
    
    if (members.length === 0) {
        membersContainer.innerHTML = '<span class="text-muted small">No members assigned</span>';
    } else {
        membersContainer.innerHTML = members.map(m => 
            `<span class="badge bg-primary me-1 mb-1 py-2 px-2">
                <i class="bi bi-person-fill me-1"></i>${m.full_name}
            </span>`
        ).join('');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
