<?php
// public/admin/dashboard.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Nếu có check quyền admin thì mở comment dưới
// if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
//   header('Location: ../login.php'); exit;
// }

/* ===== Helpers an toàn ===== */
function hasColumn(PDO $pdo, $table, $col) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([$table, $col]);
  return (bool)$st->fetchColumn();
}
function hasTable(PDO $pdo, $table) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([$table]);
  return (bool)$st->fetchColumn();
}
function fmt_money($v) {
  return number_format((int)$v, 0, ',', '.') . 'đ';
}

$today = date('Y-m-d');

/* ===== Phát hiện cột/tables ===== */
$has_total_price    = hasColumn($pdo, 'bookings', 'total_price');
$has_deposit        = hasColumn($pdo, 'bookings', 'deposit');
$has_deposit_status = hasColumn($pdo, 'bookings', 'deposit_status');
$has_booking_created= hasColumn($pdo, 'bookings', 'created_at');
$has_timeslot_id    = hasColumn($pdo, 'bookings', 'timeslot_id');
$has_timeslot_text  = hasColumn($pdo, 'bookings', 'timeslot');
$has_users_created  = hasColumn($pdo, 'users',    'created_at');
$has_timeslots_tbl  = hasTable($pdo, 'timeslots');

/* ===== 1) Thống kê chính ===== */
// Doanh thu hôm nay
if ($has_total_price) {
  $st = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE booking_date=? AND status IN ('approved','pending')");
  $st->execute([$today]);
  $revenueToday = (int)$st->fetchColumn();
} else {
  $st = $pdo->prepare("SELECT COALESCE(SUM(c.price_per_hour),0)
                       FROM bookings b JOIN courts c ON c.id=b.court_id
                       WHERE b.booking_date=? AND b.status IN ('approved','pending')");
  $st->execute([$today]);
  $revenueToday = (int)$st->fetchColumn();
}

// Đặt sân hôm nay
$st = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date=?");
$st->execute([$today]);
$bookingsToday = (int)$st->fetchColumn();

// Khách hàng mới hôm nay (nếu có created_at)
if ($has_users_created) {
  $st = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at)=?");
  $st->execute([$today]);
  $newUsersToday = (int)$st->fetchColumn();
} else {
  $newUsersToday = 0;
}

// Sân đang hoạt động
$activeCourts = (int)$pdo->query("SELECT COUNT(*) FROM courts WHERE status='active'")->fetchColumn();


// Cọc đã nhận hôm nay (nếu có cột)
$depositPaidToday = 0;
if ($has_deposit && $has_deposit_status) {
  $st = $pdo->prepare("SELECT COALESCE(SUM(deposit),0) FROM bookings WHERE booking_date=? AND deposit_status='paid'");
  $st->execute([$today]);
  $depositPaidToday = (int)$st->fetchColumn();
}

/* ===== 2) Hoạt động gần đây ===== */
$fields = array(
  "b.id","b.user_id","b.court_id","b.booking_date","b.status","c.name AS court_name"
);
$order = "ORDER BY b.id DESC";
$join  = "JOIN courts c ON c.id=b.court_id";

if ($has_timeslots_tbl && $has_timeslot_id) {
  $fields[] = "t.label AS slot_label";
  $fields[] = "t.start_time";
  $join = "JOIN courts c ON c.id=b.court_id JOIN timeslots t ON t.id=b.timeslot_id";
  $order = "ORDER BY COALESCE(b.created_at, CONCAT(b.booking_date,' ',t.start_time)) DESC, b.id DESC";
} elseif ($has_timeslots_tbl && $has_timeslot_text) {
  $fields[] = "t.label AS slot_label";
  $fields[] = "t.start_time";
  $join = "JOIN courts c ON c.id=b.court_id LEFT JOIN timeslots t ON t.label=b.timeslot";
  $order = "ORDER BY COALESCE(b.created_at, CONCAT(b.booking_date,' ',t.start_time)) DESC, b.id DESC";
} else {
  $fields[] = $has_timeslot_text ? "b.timeslot AS slot_label" : "NULL AS slot_label";
  $join = "JOIN courts c ON c.id=b.court_id";
  $order = $has_booking_created ? "ORDER BY b.created_at DESC" : "ORDER BY b.id DESC";
}

$sqlRecent = "SELECT ".implode(',', $fields)." FROM bookings b $join $order LIMIT 10";
$recent = $pdo->query($sqlRecent)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BS Badminton - Admin Panel</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    :root{--primary-color:#667eea;--primary-dark:#5a67d8;--secondary-color:#764ba2;--accent-color:#f093fb;
      --success-color:#48bb78;--warning-color:#ed8936;--error-color:#f56565;
      --bg-primary:#f7fafc;--bg-secondary:#ffffff;--text-primary:#2d3748;--text-secondary:#718096;--border-color:#e2e8f0;
      --shadow-light:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);
      --shadow-medium:0 4px 6px rgba(0,0,0,.07),0 1px 3px rgba(0,0,0,.06);
      --shadow-heavy:0 10px 25px rgba(0,0,0,.15);
      --gradient-primary:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
      --gradient-accent:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);
      --gradient-success:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);
      --gradient-warning:linear-gradient(135deg,#fa709a 0%,#fee140 100%);
    }
    [data-theme=dark]{--bg-primary:#1a202c;--bg-secondary:#2d3748;--text-primary:#f7fafc;--text-secondary:#a0aec0;--border-color:#4a5568}
    body{font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;background:var(--bg-primary);color:var(--text-primary)}
    .admin-container{display:flex;min-height:100vh}
    .sidebar{width:280px;background:var(--bg-secondary);border-right:1px solid var(--border-color);box-shadow:var(--shadow-medium);position:fixed;height:100vh;overflow-y:auto}
    .sidebar.collapsed{width:70px}
    .sidebar-header{padding:1.5rem 1rem;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:12px}
    .logo{width:40px;height:40px;background:var(--gradient-primary);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
    .logo-text{font-size:1.25rem;font-weight:700}
    .nav-menu{padding:1rem 0}
    .nav-item{margin:.25rem 0}
    .nav-link{display:flex;align-items:center;gap:12px;padding:12px 20px;color:var(--text-secondary);text-decoration:none;border-radius:0 25px 25px 0;margin-right:20px;transition:.2s}
    .nav-link:hover,.nav-link.active{background:linear-gradient(90deg,rgba(102,126,234,.1) 0%,rgba(102,126,234,.05) 100%);color:var(--primary-color);transform:translateX(8px)}
    .nav-icon{width:20px;text-align:center}
    .main-content{flex:1;margin-left:280px}
    .sidebar.collapsed + .main-content{margin-left:70px}
    .header{background:var(--bg-secondary);border-bottom:1px solid var(--border-color);padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center}
    .toggle-btn{background:none;border:none;font-size:1.2rem;color:var(--text-secondary);cursor:pointer}
    .page-title{font-size:1.5rem;font-weight:600}
    .user-info{display:flex;align-items:center;gap:12px;padding:8px 16px;background:var(--bg-primary);border-radius:25px;border:1px solid var(--border-color)}
    .user-avatar{width:32px;height:32px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600}
    .dashboard{padding:2rem}
    .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:2rem;margin-bottom:2rem}
    .stat-card{background:var(--bg-secondary);border-radius:16px;padding:2rem;border:1px solid var(--border-color);box-shadow:var(--shadow-medium)}
    .stat-icon{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;margin-bottom:.25rem}
    .revenue .stat-icon{background:var(--gradient-primary)} .bookings .stat-icon{background:var(--gradient-success)}
    .customers .stat-icon{background:var(--gradient-warning)} .active .stat-icon{background:var(--gradient-accent)}
    .stat-value{font-size:2.2rem;font-weight:700;margin:.25rem 0 .5rem}
    .stat-label{color:var(--text-secondary)}
    .content-grid{display:grid;grid-template-columns:2fr 1fr;gap:2rem}
    .card{background:var(--bg-secondary);border-radius:16px;padding:1.5rem;border:1px solid var(--border-color)}
    .recent-activity{max-height:440px;overflow:auto}
    .activity-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border-color)}
    .activity-avatar{width:36px;height:36px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;color:#fff}
    .activity-time{color:var(--text-secondary);font-size:.85rem}
    @media (max-width:992px){.content-grid{grid-template-columns:1fr}}
    @media (max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.mobile-open{transform:translateX(0)}.main-content{margin-left:0}.stats-grid{grid-template-columns:1fr}}
  </style>
</head>
<body data-theme="light">
  <div class="admin-container">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="logo"><i class="fas fa-shuttlecock"></i></div>
        <h2 class="logo-text">BS Badminton</h2>
      </div>
      <nav class="nav-menu">
        <div class="nav-item"><a class="nav-link active" href="dashboard.php"><span class="nav-icon"><i class="fas fa-chart-line"></i></span><span>Tổng quan</span></a></div>
        <div class="nav-item"><a class="nav-link" href="manage_bookings.php"><span class="nav-icon"><i class="fas fa-calendar-alt"></i></span><span>Đặt sân</span></a></div>
        <div class="nav-item"><a class="nav-link" href="users.php"><span class="nav-icon"><i class="fas fa-users"></i></span><span>Quản lý User</span></a></div>
        <div class="nav-item"><a class="nav-link" href="courts.php"><span class="nav-icon"><i class="fas fa-map-marker-alt"></i></span><span>Quản lý sân</span></a></div>
        <div class="nav-item"><a class="nav-link" href="stats.php"><span class="nav-icon"><i class="fas fa-dollar-sign"></i></span><span>Doanh thu</span></a></div>
        <div class="nav-item"><a class="nav-link" href="settings.php"><span class="nav-icon"><i class="fas fa-cog"></i></span><span>Cài đặt</span></a></div>
      </nav>
      <a class="nav-link" style="margin:12px 20px;padding:12px;border:1px solid var(--border-color);border-radius:12px;text-align:center;color:#e53e3e" href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
      </a>
    </aside>

    <main class="main-content">
      <header class="header">
        <div class="header-left">
          <button class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
          <h1 class="page-title">Trang quản trị</h1>
        </div>
     <div class="user-info">
  <?php
    $displayName = $_SESSION['user']['full_name'] 
        ?? $_SESSION['user']['username'] 
        ?? 'Admin';
    $avatarLetter = strtoupper(mb_substr($displayName, 0, 1));
  ?>
  <div class="user-avatar"><?= $avatarLetter ?></div>
  <span><?= htmlspecialchars($displayName) ?></span>
</div>

      </header>

      <section class="dashboard">
        <div class="stats-grid">
          <div class="stat-card revenue">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-value"><?php echo fmt_money($revenueToday); ?></div>
            <div class="stat-label">Doanh thu hôm nay</div>
          </div>
          <div class="stat-card bookings">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-value"><?php echo number_format($bookingsToday,0,',','.'); ?></div>
            <div class="stat-label">Đặt sân hôm nay</div>
          </div>
          <div class="stat-card customers">
            <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
            <div class="stat-value"><?php echo number_format($newUsersToday,0,',','.'); ?></div>
            <div class="stat-label">Khách hàng mới</div>
          </div>
          <div class="stat-card active">
            <div class="stat-icon"><i class="fas fa-play-circle"></i></div>
            <div class="stat-value"><?php echo number_format($activeCourts,0,',','.'); ?></div>
            <div class="stat-label">Sân đang hoạt động</div>
          </div>
        </div>

        <div class="content-grid">
          <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <h3 class="card-title">Tổng quan hôm nay</h3>
              <?php if ($has_deposit && $has_deposit_status): ?>
                <div class="stat-label">Cọc đã nhận: <strong><?php echo fmt_money($depositPaidToday); ?></strong></div>
              <?php endif; ?>
            </div>
            <div style="min-height:160px;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);">
              (Bạn có thể gắn chart JS ở đây sau)
            </div>
          </div>

          <div class="card">
            <h3 class="card-title" style="margin-bottom:12px;">Hoạt động gần đây</h3>
            <div class="recent-activity">
              <?php if (empty($recent)): ?>
                <div class="activity-item"><div class="activity-content">Chưa có hoạt động.</div></div>
              <?php else: foreach ($recent as $row): 
                $slotLabel = isset($row['slot_label']) && $row['slot_label'] ? ' — '.htmlspecialchars($row['slot_label']) : '';
              ?>
                <div class="activity-item">
                  <div class="activity-avatar">B</div>
                  <div class="activity-content">
                    <div class="activity-text">
                      Đơn #<?php echo (int)$row['id']; ?>: 
                      <?php echo htmlspecialchars($row['court_name']); ?> 
                      (<?php echo date('d/m', strtotime($row['booking_date'])); ?>)<?php echo $slotLabel; ?>
                    </div>
                    <div class="activity-time">
                      <?php
                        if ($has_booking_created && !empty($row['created_at'])) {
                          echo 'Tạo lúc ' . date('H:i d/m', strtotime($row['created_at']));
                        } else {
                          echo 'Ngày ' . date('d/m/Y', strtotime($row['booking_date']));
                        }
                      ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    document.getElementById('toggleSidebar').addEventListener('click', function(){
      document.getElementById('sidebar').classList.toggle('collapsed');
    });
  </script>
</body>
</html>
