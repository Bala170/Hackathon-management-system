<?php
/**
 * HackCommand – Registration Page (Advanced Design)
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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
            $stmt->execute([$role]);
            $roleId = $stmt->fetchColumn();

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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            display: flex;
            overflow-x: hidden;
        }
        .auth-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        /* Left branding panel */
        .auth-brand {
            flex: 1;
            background: linear-gradient(135deg, rgba(6, 214, 160, 0.1) 0%, rgba(67, 97, 238, 0.15) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        .auth-brand::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 214, 160, 0.25) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            animation: pulse-glow 4s ease-in-out infinite;
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.2) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            animation: pulse-glow 4s ease-in-out infinite 2s;
        }
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .brand-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
        }
        .brand-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #06d6a0, #4361ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .brand-title span {
            background: linear-gradient(90deg, #06d6a0, #4361ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-tagline {
            font-size: 1.1rem;
            opacity: 0.7;
            max-width: 300px;
        }
        .steps-list {
            margin-top: 3rem;
            text-align: left;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
            opacity: 0.9;
        }
        .step-num {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #06d6a0, #4361ee);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .step-text { font-size: 0.95rem; }
        /* Right form panel */
        .auth-form-panel {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        .auth-card h2 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .auth-card .subtitle {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1.5rem;
        }
        /* Floating labels */
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-floating .form-control,
        .form-floating .form-select {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #fff;
            height: 56px;
            padding: 1rem 1rem 0.5rem 3rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-floating .form-select {
            padding-top: 1.5rem;
        }
        .form-floating .form-select option {
            background: #1a1a2e;
            color: #fff;
        }
        .form-floating .form-control:focus,
        .form-floating .form-select:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #06d6a0;
            box-shadow: 0 0 0 4px rgba(6, 214, 160, 0.2);
            outline: none;
        }
        .form-floating .form-control::placeholder { color: transparent; }
        .form-floating label {
            color: rgba(255, 255, 255, 0.5);
            padding-left: 3rem;
            transition: all 0.2s ease;
        }
        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label,
        .form-floating .form-select ~ label {
            color: #06d6a0;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 1rem;
            z-index: 5;
            transition: color 0.3s ease;
        }
        .form-floating:focus-within .input-icon {
            color: #06d6a0;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            z-index: 5;
            transition: color 0.3s ease;
        }
        .password-toggle:hover { color: #fff; }
        /* Role selector pills */
        .role-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .role-option {
            flex: 1;
            text-align: center;
        }
        .role-option input { display: none; }
        .role-option label {
            display: block;
            padding: 0.75rem 0.5rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .role-option label i {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .role-option input:checked + label {
            background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(67, 97, 238, 0.2));
            border-color: #06d6a0;
            color: #fff;
        }
        /* Buttons */
        .btn-auth {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #06d6a0 0%, #4361ee 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        .btn-auth:hover::before { left: 100%; }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(6, 214, 160, 0.4);
        }
        .btn-auth:active { transform: translateY(0); }
        /* Alerts */
        .alert-glass {
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-glass.error {
            background: rgba(239, 71, 111, 0.15);
            border: 1px solid rgba(239, 71, 111, 0.3);
            color: #ff6b8a;
        }
        .alert-glass.success {
            background: rgba(6, 214, 160, 0.15);
            border: 1px solid rgba(6, 214, 160, 0.3);
            color: #06d6a0;
        }
        /* Links */
        .auth-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .auth-link:hover { color: #06d6a0; }
        .auth-link span { color: #06d6a0; font-weight: 600; }
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.25rem 0;
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        /* Responsive */
        @media (max-width: 992px) {
            .auth-brand { display: none; }
            .auth-form-panel { padding: 1rem; }
        }
        @media (max-width: 576px) {
            .auth-card { padding: 1.5rem; }
            .role-option label { font-size: 0.75rem; padding: 0.5rem; }
            .role-option label i { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Branding -->
        <div class="auth-brand" data-aos="fade-right" data-aos-duration="800">
            <div class="brand-content">
                <div class="brand-logo"><i class="bi bi-person-plus-fill"></i></div>
                <h1 class="brand-title">Join <span>HackCommand</span></h1>
                <p class="brand-tagline">Create your account in a few simple steps</p>
                <div class="steps-list">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-text">Choose your role and fill in details</div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-text">Verify your email address</div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-text">Join your team and start hacking!</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form -->
        <div class="auth-form-panel">
            <div class="auth-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                <h2>Create Account</h2>
                <p class="subtitle">Join the hackathon command center</p>

                <?php if ($error): ?>
                <div class="alert-glass error" data-aos="shake">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= sanitize($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert-glass success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= sanitize($success) ?>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="ms-2 text-white fw-bold">Sign in now →</a>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Role Selector -->
                    <p class="text-white-50 mb-2" style="font-size: 0.85rem;">I am a:</p>
                    <div class="role-selector">
                        <div class="role-option">
                            <input type="radio" name="role" id="role_student" value="student"
                                   <?= (!isset($_POST['role']) || $_POST['role'] === 'student') ? 'checked' : '' ?>>
                            <label for="role_student">
                                <i class="bi bi-mortarboard-fill"></i>
                                Student
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" id="role_coordinator" value="coordinator"
                                   <?= (isset($_POST['role']) && $_POST['role'] === 'coordinator') ? 'checked' : '' ?>>
                            <label for="role_coordinator">
                                <i class="bi bi-clipboard-check-fill"></i>
                                Coordinator
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" id="role_admin" value="admin"
                                   <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'checked' : '' ?>>
                            <label for="role_admin">
                                <i class="bi bi-shield-lock-fill"></i>
                                Admin
                            </label>
                        </div>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="bi bi-person-fill input-icon"></i>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               placeholder="Full Name" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                        <label for="full_name">Full Name</label>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Password" required>
                        <label for="password">Password (min 6 chars)</label>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               placeholder="Confirm" required>
                        <label for="confirm_password">Confirm Password</label>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-auth">
                        <i class="bi bi-person-plus-fill me-2"></i>Create Account
                    </button>
                </form>

                <div class="divider">or</div>

                <div class="text-center">
                    <a href="<?= BASE_URL ?>/auth/login.php" class="auth-link">
                        Already have an account? <span>Sign In</span>
                    </a>
                </div>

                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/" class="auth-link">
                        <i class="bi bi-arrow-left me-1"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, duration: 600 });

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        }
    </script>
</body>
</html>
