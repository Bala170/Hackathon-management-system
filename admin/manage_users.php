<?php
/**
 * HackCommand – Manage Users (Admin)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$csrf = generateCsrfToken();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId   = (int) ($_POST['role_id'] ?? 2);
        $teamId   = !empty($_POST['team_id']) ? (int) $_POST['team_id'] : null;

        if ($fullName && $email && $password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id, team_id) VALUES (?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$fullName, $email, $hashed, $roleId, $teamId]);
                setFlash('success', 'User added successfully.');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    setFlash('danger', 'Email already exists.');
                } else {
                    setFlash('danger', 'Error adding user.');
                }
            }
        }
    } elseif ($action === 'edit') {
        $userId   = (int) ($_POST['user_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $roleId   = (int) ($_POST['role_id'] ?? 2);
        $teamId   = !empty($_POST['team_id']) ? (int) $_POST['team_id'] : null;

        if ($userId && $fullName && $email) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role_id = ?, team_id = ? WHERE id = ?");
            try {
                $stmt->execute([$fullName, $email, $roleId, $teamId, $userId]);
                // Update password only if provided
                if (!empty($_POST['password'])) {
                    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $userId]);
                }
                setFlash('success', 'User updated successfully.');
            } catch (PDOException $e) {
                setFlash('danger', 'Error updating user.');
            }
        }
    } elseif ($action === 'delete') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId && $userId !== $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            setFlash('success', 'User deleted successfully.');
        } else {
            setFlash('danger', 'Cannot delete your own account.');
        }
    }
    redirect(BASE_URL . '/admin/manage_users.php');
}

// Fetch data
$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll();
$teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();

$filterRole = $_GET['role'] ?? '';
$query = "SELECT u.*, r.role_name, t.team_name FROM users u JOIN roles r ON u.role_id = r.id LEFT JOIN teams t ON u.team_id = t.id";
if ($filterRole) {
    $query .= " WHERE r.role_name = ?";
    $stmt = $pdo->prepare($query . " ORDER BY u.created_at DESC");
    $stmt->execute([$filterRole]);
} else {
    $stmt = $pdo->query($query . " ORDER BY u.created_at DESC");
}
$users = $stmt->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

        <div class="main-content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-people-fill me-2"></i>Manage Users</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus-fill me-1"></i>Add User
                </button>
            </div>

            <?= showFlash() ?>

            <!-- Filter -->
            <div class="mb-3">
                <div class="btn-group btn-group-sm">
                    <a href="?role=" class="btn btn-outline-secondary <?= !$filterRole ? 'active' : '' ?>">All</a>
                    <?php foreach ($roles as $r): ?>
                    <a href="?role=<?= $r['role_name'] ?>" class="btn btn-outline-secondary <?= $filterRole === $r['role_name'] ? 'active' : '' ?>">
                        <?= ucfirst($r['role_name']) ?>s
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card content-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Team</th><th>Joined</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($users as $i => $u): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= sanitize($u['full_name']) ?></strong></td>
                                    <td><?= sanitize($u['email']) ?></td>
                                    <td><span class="badge bg-<?= $u['role_name'] === 'admin' ? 'danger' : ($u['role_name'] === 'coordinator' ? 'warning text-dark' : 'primary') ?>"><?= ucfirst($u['role_name']) ?></span></td>
                                    <td><?= $u['team_name'] ? sanitize($u['team_name']) : '<span class="text-muted">—</span>' ?></td>
                                    <td class="small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="<?= $u['id'] ?>" data-name="<?= sanitize($u['full_name']) ?>"
                                            data-email="<?= sanitize($u['email']) ?>" data-role="<?= $u['role_id'] ?>"
                                            data-team="<?= $u['team_id'] ?? '' ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash-fill"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= ucfirst($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Team (optional)</label>
                        <select name="team_id" class="form-select">
                            <option value="">— No Team —</option>
                            <?php foreach ($teams as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= sanitize($t['team_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" id="editRole" class="form-select">
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= ucfirst($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Team</label>
                        <select name="team_id" id="editTeam" class="form-select">
                            <option value="">— No Team —</option>
                            <?php foreach ($teams as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= sanitize($t['team_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editUserModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editUserId').value = btn.dataset.id;
    document.getElementById('editName').value = btn.dataset.name;
    document.getElementById('editEmail').value = btn.dataset.email;
    document.getElementById('editRole').value = btn.dataset.role;
    document.getElementById('editTeam').value = btn.dataset.team || '';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
