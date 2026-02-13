        </div><!-- /.main-content -->
    </div><!-- /.d-flex -->
</div><!-- /.container-fluid -->

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (for AJAX) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?php if (isset($_SESSION['user_id'])): ?>
<script src="<?= BASE_URL ?>/assets/js/announcements.js"></script>
<?php endif; ?>
<?php if (isset($includeLeaderboardJs) && $includeLeaderboardJs): ?>
<script src="<?= BASE_URL ?>/assets/js/leaderboard.js"></script>
<?php endif; ?>
</body>
</html>
