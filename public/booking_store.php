<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../middleware/auth.php';
require_once __DIR__.'/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
  http_response_code(405); 
  exit; 
}

csrf_check();

session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$user_id    = $_SESSION['user']['id'] ?? 0;
$court_id   = (int)($_POST['court_id'] ?? 0);
$day        = trim($_POST['booking_date'] ?? '');
$start_time = trim($_POST['start_time'] ?? '');
$end_time   = trim($_POST['end_time'] ?? '');
$notes      = trim($_POST['notes'] ?? '');

if (!$court_id || !$day || !$start_time || !$end_time) { 
  flash('error', 'Thiếu thông tin đặt sân.'); 
  header('Location: '.BASE_URL.'/booking.php'); 
  exit; 
}

// ✅ Kiểm tra định dạng giờ
if (strtotime($end_time) <= strtotime($start_time)) {
  flash('error', '⛔ Giờ kết thúc phải lớn hơn giờ bắt đầu.');
  header('Location: '.BASE_URL.'/booking.php');
  exit;
}

// ✅ Kiểm tra không cho đặt giờ trong quá khứ
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

if ($day < $currentDate) {
  flash('error', '⛔ Ngày đặt sân đã qua.');
  header('Location: '.BASE_URL.'/booking.php');
  exit;
}
if ($day === $currentDate && $start_time <= $currentTime) {
  flash('error', '⛔ Khung giờ này đã qua.');
  header('Location: '.BASE_URL.'/booking.php');
  exit;
}

try {
  $pdo->beginTransaction();

  // 🔍 1️⃣ Kiểm tra trùng lịch (khoảng giờ chồng nhau)
  $st = $pdo->prepare("
      SELECT id 
      FROM bookings 
      WHERE court_id = ? 
        AND booking_date = ? 
        AND status IN ('pending','approved') 
        AND NOT (end_time <= ? OR start_time >= ?)
      FOR UPDATE
  ");
  // => Không trùng nếu: end_time <= start_time_mới OR start_time >= end_time_mới
  $st->execute([$court_id, $day, $start_time, $end_time]);
  if ($st->fetch()) {
    $pdo->rollBack();
    flash('error', '⚠️ Thời gian này đã có người đặt trước.');
    header('Location: '.BASE_URL.'/booking.php?court_id='.$court_id);
    exit;
  }

  // 💰 2️⃣ Lấy giá sân
  $st = $pdo->prepare("SELECT price_per_hour FROM courts WHERE id=? AND status='active'");
  $st->execute([$court_id]);
  $court = $st->fetch(PDO::FETCH_ASSOC);

  if (!$court) {
    $pdo->rollBack();
    flash('error', '❌ Sân không tồn tại hoặc đã bị khóa.');
    header('Location: '.BASE_URL.'/index.php');
    exit;
  }

  $pricePerHour = (float)$court['price_per_hour'];

  // 🕒 3️⃣ Tính tiền theo giờ lẻ
  $minutes = (strtotime($end_time) - strtotime($start_time)) / 60;
  $hours = $minutes / 60.0;
  $total_price = round($pricePerHour * $hours);
  $deposit = round($total_price * 0.15);

  // 🧾 4️⃣ Lưu đơn đặt sân
  $ins = $pdo->prepare("
      INSERT INTO bookings 
      (user_id, court_id, booking_date, start_time, end_time, total_price, deposit, deposit_status, status, notes, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, NOW())
  ");
  $ins->execute([$user_id, $court_id, $day, $start_time, $end_time, $total_price, $deposit, $notes]);

  $lastId = $pdo->lastInsertId();

  // ✅ 5️⃣ Hoàn tất
  $pdo->commit();
  flash('success', '✅ Đặt sân thành công! Vui lòng thanh toán cọc 15% để giữ sân.');
  header('Location: '.BASE_URL.'/payment.php?id='.$lastId);
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  flash('error', 'Lỗi hệ thống: '.htmlspecialchars($e->getMessage()));
  header('Location: '.BASE_URL.'/booking.php');
  exit;
}
