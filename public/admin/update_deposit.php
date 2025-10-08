<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// ✅ Kiểm tra CSRF
if (
  !isset($_POST['csrf_token']) ||
  !isset($_SESSION['csrf_token']) ||
  $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
  header("Location: manage_bookings.php?error=CSRF token không hợp lệ");
  exit;
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
if ($booking_id <= 0) {
  header("Location: manage_bookings.php?error=ID đơn không hợp lệ");
  exit;
}

try {
  // ✅ Cập nhật trạng thái sang "paid"
  $st = $pdo->prepare("UPDATE bookings SET status='paid' WHERE id=?");
  $st->execute([$booking_id]);

  header("Location: manage_bookings.php?success=1");
  exit;
} catch (PDOException $e) {
  header("Location: manage_bookings.php?error=" . urlencode($e->getMessage()));
  exit;
}
