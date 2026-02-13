<?php
/**
 * HackCommand – Leaderboard (Student)
 */
require_once __DIR__ . '/../includes/header.php';
requireRole('student');
$includeLeaderboardJs = true;

$teamId = $_SESSION['team_id'];

$leaderboard = $pdo->query("
    SELECT t.id, t.team_name,
           COALESCE(SUM(s.total), 0) as total_score,
           COUNT(s.id) as judge_count,
           COALESCE(AVG(s.total), 0) as avg_score
    FROM teams t
    LEFT JOIN scores s ON t.id = s.team_id
    GROUP BY t.id, t.team_name
    ORDER BY total_score DESC, (COALESCE(SUM(s.innovation), 0) + COALESCE(SUM(s.technical), 0)) DESC
")->fetchAll();
?>

<div class="container-fluid mt-2">
    <div class="d-flex">
        <?php include __DIR__ . '/../includes/sidebar_student.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <h4><i class="bi bi-trophy-fill me-2"></i>Leaderboard</h4>
                <small class="text-muted">Auto-refreshes every 15 seconds</small>
            </div>

            <div class="card content-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table leaderboard-table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:80px">Rank</th>
                                    <th>Team</th>
                                    <th class="text-center">Total Score</th>
                                    <th class="text-center">Judges</th>
                                    <th class="text-center">Avg Score</th>
                                </tr>
                            </thead>
                            <tbody id="leaderboardBody">
                            <?php
                            $currentRank = 0;
                            $prevScore = -1;
                            foreach ($leaderboard as $i => $team):
                                if ((float)$team['total_score'] !== $prevScore) {
                                    $currentRank = $i + 1;
                                }
                                $prevScore = (float)$team['total_score'];
                                $rankClass = $currentRank <= 3 ? 'rank-' . $currentRank : 'rank-other';
                                $rowClass  = $currentRank <= 3 ? 'leaderboard-row-' . $currentRank : '';
                                if ($team['id'] == $teamId) $rowClass .= ' table-primary';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="text-center"><span class="rank-badge <?= $rankClass ?>"><?= $currentRank ?></span></td>
                                <td>
                                    <strong><?= sanitize($team['team_name']) ?></strong>
                                    <?= $team['id'] == $teamId ? '<span class="badge bg-primary ms-1">Your Team</span>' : '' ?>
                                    <?php if ($currentRank === 1): ?><i class="bi bi-trophy-fill text-warning"></i><?php endif; ?>
                                    <?php if ($currentRank === 2): ?><i class="bi bi-award-fill" style="color:silver"></i><?php endif; ?>
                                    <?php if ($currentRank === 3): ?><i class="bi bi-award-fill" style="color:#cd7f32"></i><?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge bg-primary fs-6"><?= number_format($team['total_score'], 1) ?></span></td>
                                <td class="text-center"><?= $team['judge_count'] ?></td>
                                <td class="text-center"><?= number_format($team['avg_score'], 1) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($leaderboard)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-trophy display-6 d-block mb-2 opacity-50"></i>No scores recorded yet</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
