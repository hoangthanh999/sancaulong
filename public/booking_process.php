<?php
require_once __DIR__.'/../config/db.php';
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$user_id = $_SESSION['user']['id'] ?? 0;
$court_id = (int)($_POST['court_id'] ?? 0);
$date = trim($_POST['booking_date'] ?? '');
$start_time = trim($_POST['start_time'] ?? '');
$end_time = trim($_POST['end_time'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (!$user_id || !$court_id || !$date || !$start_time || !$end_time) {
    $_SESSION['flash_error'] = 'Thiếu thông tin đặt sân.';
    header("Location: booking.php");
    exit;
}

// ✅ Kiểm tra định dạng giờ
if (strtotime($end_time) <= strtotime($start_time)) {
    $_SESSION['flash_error'] = '⛔ Giờ kết thúc phải lớn hơn giờ bắt đầu.';
    header("Location: booking.php");
    exit;
}

// ✅ Kiểm tra giờ trong quá khứ
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

if ($date < $currentDate) {
    $_SESSION['flash_error'] = '⛔ Ngày đặt sân đã qua.';
    header("Location: booking.php");
    exit;
}
if ($date === $currentDate && $start_time <= $currentTime) {
    $_SESSION['flash_error'] = '⛔ Khung giờ này đã qua.';
    header("Location: booking.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // ✅ Kiểm tra trùng lịch
    $chk = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE court_id=? AND booking_date=? 
          AND status IN ('pending','approved')
          AND NOT (end_time <= ? OR start_time >= ?)
    ");
    // logic: nếu end <= start_mới hoặc start >= end_mới thì KHÔNG trùng
    $chk->execute([$court_id, $date, $start_time, $end_time]);
    if ($chk->fetch()) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = '⚠️ Thời gian này đã có người đặt!';
        header("Location: booking.php");
        exit;
    }

    // ✅ Lấy giá sân
    $st = $pdo->prepare("SELECT price_per_hour FROM courts WHERE id=? AND status='active'");
    $st->execute([$court_id]);
    $court = $st->fetch(PDO::FETCH_ASSOC);
    if (!$court) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Sân không tồn tại hoặc đã khóa.';
        header("Location: booking.php");
        exit;
    }

    $pricePerHour = (float)$court['price_per_hour'];

    // ✅ Tính tổng tiền theo phút
    $minutes = (strtotime($end_time) - strtotime($start_time)) / 60;
    $hours = $minutes / 60.0;
    $total_price = round($pricePerHour * $hours);

    // ✅ Thêm đặt sân
    $ins = $pdo->prepare("
        INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, total_price, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $ins->execute([$user_id, $court_id, $date, $start_time, $end_time, $total_price]);

    $pdo->commit();
    $_SESSION['flash_success'] = '✅ Đặt sân thành công! Tổng tiền: ' . number_format($total_price, 0, ',', '.') . ' VNĐ';
    header("Location: booking.php");
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['flash_error'] = 'Lỗi hệ thống: ' . $e->getMessage();
    header("Location: booking.php");
    exit;
}
