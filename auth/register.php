<?php
/**
 * HackCommand – User Registration (Admin / Student / Coordinator)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/' . $_SESSION['role'] . '/dashboard.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? 'student');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $allowedRoles = ['admin', 'student', 'coordinator'];

    // Validation
    if (empty($fullName) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!in_array($role, $allowedRoles)) {
        $error = 'Invalid role selected.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            // Get selected role id
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
            $stmt->execute([$role]);
            $roleId = $stmt->fetchColumn();

            // Insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullName, $email, $hashedPassword, $roleId]);

            $success = 'Registration successful! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – HackCommand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-terminal-fill display-3 text-primary"></i>
                            <h3 class="mt-2 fw-bold">HackCommand</h3>
                            <p class="text-muted">Create your account</p>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><?= sanitize($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i><?= sanitize($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                           value="<?= sanitize($_POST['full_name'] ?? '') ?>"
                                           placeholder="John Doe" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Register As</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-fill"></i></span>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="student" <?= (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : '' ?>>Student</option>
                                        <option value="coordinator" <?= (isset($_POST['role']) && $_POST['role'] === 'coordinator') ? 'selected' : '' ?>>Coordinator</option>
                                        <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?= sanitize($_POST['email'] ?? '') ?>"
                                           placeholder="you@example.com" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Min 6 characters" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                           placeholder="Re-enter password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">
                                <i class="bi bi-person-plus-fill me-1"></i>Register
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/auth/login.php" class="text-decoration-none small">
                                Already have an account? Sign In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
