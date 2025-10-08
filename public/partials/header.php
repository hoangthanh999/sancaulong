<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;

/* Base URL tới thư mục /public, ví dụ: /web-san-cau/public */
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($BASE === '') $BASE = '.';
$BASE_ESC = htmlspecialchars($BASE, ENT_QUOTES);
$VER = time(); // cache-busting để tránh dính cache cũ
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BS Badminton</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- CSS dự án: CHÚ Ý đúng thư mục assets/css -->
  <link rel="stylesheet" href="<?= $BASE_ESC ?>/assets/css/all.min.css?v=<?= $VER ?>"/>
  <link rel="stylesheet" href="<?= $BASE_ESC ?>/assets/css/style.css?v=<?= $VER ?>" id="css-main"/>
  <link rel="stylesheet" href="<?= $BASE_ESC ?>/assets/css/style-home.css?v=<?= $VER ?>" id="css-home"/>
</head>
<body>
<header class="header">
  <nav class="nav-container d-flex align-items-center justify-content-between">
    <a href="<?= $BASE_ESC ?>/index.php" class="logo text-decoration-none">BS Badminton</a>

    <ul class="nav-links list-unstyled d-flex gap-3 m-0">
      <li><a href="<?= $BASE_ESC ?>/index.php"      class="<?= basename($_SERVER['PHP_SELF'])==='index.php'?'active':'' ?>">Trang chủ</a></li>
      <li><a href="<?= $BASE_ESC ?>/booking.php"     class="<?= basename($_SERVER['PHP_SELF'])==='booking.php'?'active':'' ?>">Đặt sân</a></li>
      <li><a href="<?= $BASE_ESC ?>/my_bookings.php" class="<?= basename($_SERVER['PHP_SELF'])==='my_bookings.php'?'active':'' ?>">Đơn của tôi</a></li>

      <!-- Chat cộng đồng (trang cũ của bạn) -->
      <li><a href="<?= $BASE_ESC ?>/chat.php"        class="<?= basename($_SERVER['PHP_SELF'])==='chat.php'?'active':'' ?>">Cộng đồng</a></li>

      <!-- Chat CSKH (mở bong bóng) -->
      <li><a href="#" id="openSupportChatLink">Chat CSKH</a></li>

      <li><a href="<?= $BASE_ESC ?>/profile.php"     class="<?= basename($_SERVER['PHP_SELF'])==='profile.php'?'active':'' ?>">Tài khoản</a></li>
    </ul>

    <div class="user-actions d-flex align-items-center gap-2">
      <a href="#" id="openSupportChatBtn" class="btn btn-outline"><i class="fa-solid fa-comments"></i> Chat</a>
      <?php if ($user): ?>
        <a href="<?= $BASE_ESC ?>/logout.php" class="btn btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
      <?php else: ?>
        <a href="<?= $BASE_ESC ?>/login.php" class="btn btn-primary">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<!-- Hai nút trên navbar mở bong bóng -->
<script>
(function(){
  function openBubble(e){
    e && e.preventDefault();
    var t = document.querySelector('#chat-launcher .fab');
    if (t) t.click(); // mở menu launcher; người dùng chọn "Liên hệ CSKH"
  }
  document.addEventListener('DOMContentLoaded', function(){
    var a1 = document.getElementById('openSupportChatLink');
    var a2 = document.getElementById('openSupportChatBtn');
    a1 && a1.addEventListener('click', openBubble);
    a2 && a2.addEventListener('click', openBubble);
  });
})();
</script>
