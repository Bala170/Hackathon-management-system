<?php
/**
 * HackCommand – Landing Page
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If logged in, redirect to role dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirect(BASE_URL . '/' . $_SESSION['role'] . '/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackCommand – Smart Hackathon Operations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: #000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 0;
            position: relative;
            overflow: hidden;
        }
        .hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
            z-index: 0;
            opacity: 1;
        }
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
        .hero-title { font-size: 3.5rem; font-weight: 800; }
        .hero-subtitle { font-size: 1.25rem; opacity: 0.85; }
        .feature-icon {
            width: 64px; height: 64px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px; font-size: 1.5rem;
            background: rgba(67, 97, 238, 0.15); color: #4361ee;
        }
        .glow-text {
            background: linear-gradient(90deg, #4361ee, #7209b7, #f72585);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body style="padding-top: 0;">
    <div class="hero-section text-white" data-aos="fade-in">
        <!-- Background Video -->
        <video class="hero-video" autoplay muted loop playsinline poster="<?= BASE_URL ?>/assets/img/hero-poster.jpg">
            <source src="BG.mp4" type="video/mp4">
            <!-- Fallback: black background shows if video fails -->
        </video>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-up">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-terminal-fill display-5 me-3 text-primary"></i>
                        
                    </div>
                    <h1 class="hero-title mb-3">
                        <span class="glow-text">Hack</span>Command
                    </h1>
                    <p class="hero-subtitle mb-4" data-aos="fade-up" data-aos-delay="100">
                        The Smart Hackathon Operations Command Center.<br>
                        Communication. Logistics. Evaluation. Leaderboard.<br>
                        <strong>All in one unified platform.</strong>
                    </p>
                    <div class="d-flex gap-3 flex-wrap" data-aos="zoom-in" data-aos-delay="200">
                        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-gradient btn-lg px-5 py-3 hover-lift">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </a>
                        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-outline-light btn-lg px-5 py-3 hover-lift">
                            <i class="bi bi-person-plus-fill me-2"></i>Register
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="150">
                    <div class="row g-3">
                        <div class="col-6" data-aos="fade-up" data-aos-delay="100">
                            <div class="card bg-white bg-opacity-10 border-0 text-white p-3 h-100 hover-lift">
                                <div class="feature-icon mb-3"><i class="bi bi-megaphone-fill"></i></div>
                                <h6>Real-Time Announcements</h6>
                                <small class="opacity-75">No more WhatsApp delays. Instant in-platform notifications.</small>
                            </div>
                        </div>
                        <div class="col-6" data-aos="fade-up" data-aos-delay="200">
                            <div class="card bg-white bg-opacity-10 border-0 text-white p-3 h-100 hover-lift">
                                <div class="feature-icon mb-3"><i class="bi bi-building"></i></div>
                                <h6>Smart Hall Allocation</h6>
                                <small class="opacity-75">Know your hall, seat, and navigate with Google Maps.</small>
                            </div>
                        </div>
                        <div class="col-6" data-aos="fade-up" data-aos-delay="300">
                            <div class="card bg-white bg-opacity-10 border-0 text-white p-3 h-100 hover-lift">
                                <div class="feature-icon mb-3"><i class="bi bi-star-fill"></i></div>
                                <h6>Transparent Scoring</h6>
                                <small class="opacity-75">Multi-criteria judge scoring with duplicate prevention.</small>
                            </div>
                        </div>
                        <div class="col-6" data-aos="fade-up" data-aos-delay="400">
                            <div class="card bg-white bg-opacity-10 border-0 text-white p-3 h-100 hover-lift">
                                <div class="feature-icon mb-3"><i class="bi bi-trophy-fill"></i></div>
                                <h6>Live Leaderboard</h6>
                                <small class="opacity-75">Dynamic rankings that update in real time.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer pinned to bottom -->
        <footer class="position-absolute bottom-0 start-0 w-100 text-center py-3" style="z-index: 2;">
            <small class="text-white-50">&copy; 2026 Agentic Bro. Built for NeuroCode Hackathon.</small>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
