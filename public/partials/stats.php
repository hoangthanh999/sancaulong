<section class="stats-section">
  <h2 class="section-title">Thống kê của bạn</h2>
  <div class="stats-grid">

    <div class="stat-card bookings">
      <div class="stat-icon">🏸</div>
      <div class="stat-number"><?= $totalBookings ?></div>
      <div class="stat-label">Sân đã đặt</div>
    </div>

    <div class="stat-card revenue">
      <div class="stat-icon">💰</div>
      <div class="stat-number"><?= number_format($totalSpent, 0, ',', '.') ?> VNĐ</div>
      <div class="stat-label">Tổng chi phí</div>
    </div>

    <div class="stat-card courts">
      <div class="stat-icon">🎯</div>
      <div class="stat-number"><?= $totalCourts ?></div>
      <div class="stat-label">Sân khả dụng</div>
    </div>

    <div class="stat-card customers">
      <div class="stat-icon">⭐</div>
      <div class="stat-number">4.9</div>
      <div class="stat-label">Đánh giá trung bình</div>
    </div>
    
  </div>
</section>
