<?php
/**
 * HackCommand – AJAX: Mark Announcement as Read
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'invalid method']);
    exit;
}

$annId  = (int) ($_POST['announcement_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($annId) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)");
    $stmt->execute([$annId, $userId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'missing announcement_id']);
}
