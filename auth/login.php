<?php
/**
 * HackCommand – Login Page (Advanced Design)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirect(BASE_URL . '/' . $_SESSION['role'] . '/dashboard.php');
}

$error = '';

// Handle timeout/unauthorized messages
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please log in again.';
} elseif (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'You do not have permission to access that page.';
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.id, u.full_name, u.email, u.password, u.team_id, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['email']         = $user['email'];
            $_SESSION['role']          = $user['role_name'];
            $_SESSION['team_id']       = $user['team_id'];
            $_SESSION['last_activity'] = time();
            redirect(BASE_URL . '/' . $user['role_name'] . '/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – HackCommand</title>
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
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.15) 0%, rgba(114, 9, 183, 0.15) 100%);
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
            background: radial-gradient(circle, rgba(67, 97, 238, 0.3) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation: pulse-glow 4s ease-in-out infinite;
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(114, 9, 183, 0.25) 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
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
            background: linear-gradient(135deg, #4361ee, #7209b7);
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
            background: linear-gradient(90deg, #4361ee, #7209b7, #f72585);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-tagline {
            font-size: 1.1rem;
            opacity: 0.7;
            max-width: 300px;
        }
        .brand-features {
            margin-top: 3rem;
            text-align: left;
        }
        .brand-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            opacity: 0.85;
        }
        .brand-feature i {
            font-size: 1.25rem;
            color: #4361ee;
        }
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
            max-width: 420px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        .auth-card h2 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .auth-card .subtitle {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 2rem;
        }
        /* Floating labels */
        .form-floating {
            margin-bottom: 1.25rem;
        }
        .form-floating .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #fff;
            height: 58px;
            padding: 1rem 1rem 0.5rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-floating .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #4361ee;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
            outline: none;
        }
        .form-floating .form-control::placeholder { color: transparent; }
        .form-floating label {
            color: rgba(255, 255, 255, 0.5);
            padding-left: 3rem;
            transition: all 0.2s ease;
        }
        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            color: #4361ee;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.1rem;
            z-index: 5;
            transition: color 0.3s ease;
        }
        .form-floating:focus-within .input-icon {
            color: #4361ee;
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
            background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
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
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.4);
        }
        .btn-auth:active { transform: translateY(0); }
        /* Alert */
        .alert-glass {
            background: rgba(239, 71, 111, 0.15);
            border: 1px solid rgba(239, 71, 111, 0.3);
            color: #ff6b8a;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        /* Links */
        .auth-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        .auth-link:hover { color: #4361ee; }
        .auth-link span { color: #4361ee; font-weight: 600; }
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
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
            .auth-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Branding -->
        <div class="auth-brand" data-aos="fade-right" data-aos-duration="800">
            <div class="brand-content">
                <div class="brand-logo"><i class="bi bi-terminal-fill"></i></div>
                <h1 class="brand-title"><span>Hack</span>Command</h1>
                <p class="brand-tagline">Smart Hackathon Operations Command Center</p>
                <div class="brand-features">
                    <div class="brand-feature">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>Real-time announcements</span>
                    </div>
                    <div class="brand-feature">
                        <i class="bi bi-building"></i>
                        <span>Smart hall allocation</span>
                    </div>
                    <div class="brand-feature">
                        <i class="bi bi-trophy-fill"></i>
                        <span>Live leaderboard</span>
                    </div>
                    <div class="brand-feature">
                        <i class="bi bi-shield-check"></i>
                        <span>Transparent scoring</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form -->
        <div class="auth-form-panel">
            <div class="auth-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                <h2>Welcome Back</h2>
                <p class="subtitle">Sign in to continue to your dashboard</p>

                <?php if ($error): ?>
                <div class="alert-glass" data-aos="shake">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= sanitize($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="form-floating position-relative">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Email" value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
                        <label for="email">Email Address</label>
                    </div>

                    <div class="form-floating position-relative">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Password" required>
                        <label for="password">Password</label>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-auth">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>

                <div class="divider">or</div>

                <div class="text-center">
                    <a href="<?= BASE_URL ?>/auth/register.php" class="auth-link">
                        Don't have an account? <span>Create one</span>
                    </a>
                </div>

                <div class="text-center mt-4">
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
