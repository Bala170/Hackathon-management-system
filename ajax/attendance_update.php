<?php
/**
 * HackCommand – AJAX: Attendance Update
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coordinator') {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'invalid method']);
    exit;
}

$userId = (int) ($_POST['user_id'] ?? 0);
$hallId = (int) ($_POST['hall_id'] ?? 0);
$status = $_POST['status'] ?? '';
$date   = $_POST['date'] ?? date('Y-m-d');

if (!$userId || !$hallId || !in_array($status, ['present', 'absent'])) {
    echo json_encode(['error' => 'missing or invalid parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND hall_id = ? AND date = ?");
    $stmt->execute([$userId, $hallId, $date]);

    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE attendance SET status = ?, marked_by = ? WHERE user_id = ? AND hall_id = ? AND date = ?")
            ->execute([$status, $_SESSION['user_id'], $userId, $hallId, $date]);
    } else {
        $pdo->prepare("INSERT INTO attendance (user_id, hall_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)")
            ->execute([$userId, $hallId, $date, $status, $_SESSION['user_id']]);
    }

    echo json_encode(['success' => true, 'status' => $status]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to update attendance']);
}
