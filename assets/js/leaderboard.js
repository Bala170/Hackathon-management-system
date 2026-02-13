/**
 * HackCommand – Dynamic Leaderboard AJAX
 * Refreshes leaderboard data every 15 seconds
 */
(function() {
    const BASE_URL = document.querySelector('meta[name="base-url"]')?.content ||
                     window.location.pathname.split('/').slice(0, 2).join('/');

    function fetchLeaderboard() {
        const container = document.getElementById('leaderboardBody');
        if (!container) return;

        $.ajax({
            url: BASE_URL + '/ajax/fetch_leaderboard.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && data.leaderboard) {
                    renderLeaderboard(data.leaderboard);
                }
            },
            error: function() {
                // Silently fail
            }
        });
    }

    function renderLeaderboard(teams) {
        const tbody = document.getElementById('leaderboardBody');
        if (!tbody) return;

        let html = '';
        let currentRank = 0;
        let prevScore = -1;
        let skipCount = 0;

        teams.forEach(function(team, index) {
            // Handle ties: same score = same rank
            if (parseFloat(team.total_score) !== prevScore) {
                currentRank = index + 1;
                skipCount = 0;
            } else {
                skipCount++;
            }
            prevScore = parseFloat(team.total_score);

            const rankClass = currentRank <= 3 ? 'rank-' + currentRank : 'rank-other';
            const rowClass = currentRank <= 3 ? 'leaderboard-row-' + currentRank : '';
            const medal = currentRank === 1 ? '<i class="bi bi-trophy-fill text-warning"></i>' :
                          currentRank === 2 ? '<i class="bi bi-award-fill" style="color:silver"></i>' :
                          currentRank === 3 ? '<i class="bi bi-award-fill" style="color:#cd7f32"></i>' : '';

            html += '<tr class="' + rowClass + '">';
            html += '<td class="text-center"><span class="rank-badge ' + rankClass + '">' + currentRank + '</span></td>';
            html += '<td><strong>' + escapeHtml(team.team_name) + '</strong> ' + medal + '</td>';
            html += '<td class="text-center"><span class="badge bg-primary fs-6">' + parseFloat(team.total_score).toFixed(1) + '</span></td>';
            html += '<td class="text-center">' + (team.judge_count || 0) + '</td>';
            html += '<td class="text-center">' + parseFloat(team.avg_score || 0).toFixed(1) + '</td>';
            html += '</tr>';
        });

        if (html === '') {
            html = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-trophy display-6 d-block mb-2 opacity-50"></i>No scores recorded yet</td></tr>';
        }

        tbody.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Start polling
    if (typeof $ !== 'undefined') {
        fetchLeaderboard();
        setInterval(fetchLeaderboard, 15000);
    }
})();
