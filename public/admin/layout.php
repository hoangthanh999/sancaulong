<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>BS Badminton - Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex h-screen">
  
  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-lg flex flex-col">
    <div class="p-6 border-b">
      <h1 class="font-bold text-lg text-gray-800">BS Badminton</h1>
      <p class="text-sm text-gray-500">Admin Panel</p>
    </div>

    <nav class="flex-1 p-4">
      <ul class="space-y-2">
        <li><a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-blue-100"><i class="fa fa-chart-line"></i> Tổng quan</a></li>
        <li><a href="manage_bookings.php" class="block px-3 py-2 rounded hover:bg-blue-100"><i class="fa fa-calendar"></i> Đặt sân</a></li>
        <li><a href="manage_users.php" class="block px-3 py-2 rounded hover:bg-blue-100"><i class="fa fa-users"></i> Quản lý User</a></li>
        <li><a href="courts.php" class="block px-3 py-2 rounded hover:bg-blue-100"><i class="fa fa-map-marker-alt"></i> Quản lý sân</a></li>
        <li><a href="revenue.php" class="block px-3 py-2 rounded hover:bg-blue-100"><i class="fa fa-dollar-sign"></i> Doanh thu</a></li>
        <li><a href="settings.php" class="block px-3 py-2 rounded hover:bg-blue-100"><i class="fa fa-cog"></i> Cài đặt</a></li>
      </ul>
    </nav>

    <div class="p-4 border-t">
      <a href="../logout.php" class="w-full block px-3 py-2 rounded bg-red-500 text-white text-center hover:bg-red-600">
        <i class="fa fa-sign-out-alt"></i> Đăng xuất
      </a>
    </div>
  </aside>

  <!-- Main -->
  <main class="flex-1 flex flex-col">
    <header class="bg-white shadow-sm border-b px-6 py-4 flex items-center justify-between">
      <h2 class="text-xl font-bold text-gray-800">Trang quản trị</h2>
      <div class="flex items-center space-x-4">
        <span class="text-gray-600"><i class="fa fa-bell"></i></span>
        <span class="font-medium"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
      </div>
    </header>

    <div class="p-6">
