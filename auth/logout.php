<?php
/**
 * HackCommand – Logout
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "/auth/login.php");
exit;
