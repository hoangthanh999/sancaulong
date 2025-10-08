<?php
// 🔒 Đảm bảo session đã khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Lấy thông tin user nếu có
$user = $_SESSION['user'] ?? [];

// ✅ Gán mặc định để không lỗi
$fullName = trim($user['full_name'] ?? '');
$phone    = trim($user['phone'] ?? '');
$initials = strtoupper(substr($fullName !== '' ? $fullName : '??', 0, 2));
?>
<section class="welcome-section">
  <div class="welcome-content">
    <div class="welcome-text">
      <h1>Chào mừng trở lại!</h1>
      <p>Chúc bạn có những trận đấu tuyệt vời tại <b>BS Badminton</b>.</p>
      <a href="booking.php" class="btn btn-primary">Đặt sân ngay</a>
    </div>

    <div class="user-info">
      <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
      <div class="user-name"><?= htmlspecialchars($fullName ?: 'Khách') ?></div>
      <div class="user-phone">
        📞 <?= htmlspecialchars($phone ?: 'Chưa cập nhật') ?>
      </div>
      <a href="profile.php" class="btn btn-outline">Cập nhật thông tin</a>
    </div>
  </div>
</section>
