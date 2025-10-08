<section class="recent-activity">
  <h2>Hoạt động gần đây</h2>
  <div class="activity-list">
    <?php if (empty($recentActivities)): ?>
      <p class="text-muted">Bạn chưa có hoạt động nào gần đây.</p>
    <?php else: ?>
      <?php foreach ($recentActivities as $a): ?>
        <div class="activity-item">
          <div class="activity-icon">🏸</div>
          <div class="activity-content">
            <div class="activity-title">
              <?= htmlspecialchars($a['court_name']) ?> - <?= htmlspecialchars($a['timeslot']) ?>
            </div>
            <div class="activity-time"><?= $a['booking_date'] ?> (<?= $a['status'] ?>)</div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
