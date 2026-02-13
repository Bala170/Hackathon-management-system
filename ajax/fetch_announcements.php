<?php
/**
 * HackCommand – AJAX: Fetch Announcements
 * Returns JSON with announcements and unread count
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Count-only mode for badge polling
if (isset($_GET['count_only'])) {
    $unread = getUnreadCount($pdo, $userId);
    echo json_encode(['unread_count' => $unread]);
    exit;
}

// Full announcement list
$stmt = $pdo->query("
    SELECT a.id, a.title, a.message, a.created_at, u.full_name as posted_by
    FROM announcements a
    JOIN users u ON a.posted_by = u.id
    ORDER BY a.created_at DESC
    LIMIT 20
");
$announcements = $stmt->fetchAll();

// Check read status for each
foreach ($announcements as &$ann) {
    $stmt = $pdo->prepare("SELECT id FROM announcement_reads WHERE announcement_id = ? AND user_id = ?");
    $stmt->execute([$ann['id'], $userId]);
    $ann['is_read'] = (bool) $stmt->fetch();
    $ann['time_ago'] = timeAgo($ann['created_at']);
}

$unread = getUnreadCount($pdo, $userId);

echo json_encode([
    'announcements' => $announcements,
    'unread_count'  => $unread
]);
