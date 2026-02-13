<?php
/**
 * HackCommand – AJAX: Fetch Leaderboard
 * Returns JSON leaderboard sorted by total score
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT t.id, t.team_name,
               COALESCE(SUM(s.total), 0) as total_score,
               COUNT(s.id) as judge_count,
               COALESCE(AVG(s.total), 0) as avg_score
        FROM teams t
        LEFT JOIN scores s ON t.id = s.team_id
        GROUP BY t.id, t.team_name
        ORDER BY total_score DESC,
                 (COALESCE(SUM(s.innovation), 0) + COALESCE(SUM(s.technical), 0)) DESC
    ");
    $leaderboard = $stmt->fetchAll();

    echo json_encode(['leaderboard' => $leaderboard]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch leaderboard']);
}
