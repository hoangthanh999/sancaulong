<?php
session_start();

// Xóa toàn bộ session
session_unset();
session_destroy();

// Tạo session tạm để báo đăng xuất
session_start();
$_SESSION['message'] = "Bạn đã đăng xuất thành công, vui lòng đăng nhập lại!";

// Chuyển về trang đăng nhập
header("Location: login.php");
exit;
