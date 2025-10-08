<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$court_id = isset($_GET['court_id']) ? (int)$_GET['court_id'] : 0;
$date     = isset($_GET['date']) ? trim($_GET['date']) : '';

if (!$court_id || !$date) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu court_id hoặc date']);
    exit;
}

try {
    // ✅ Lấy tất cả timeslots còn hoạt động
    $timeslots = $pdo->query("
        SELECT id, label, start_time, end_time
        FROM timeslots
        WHERE status = 'active'
        ORDER BY start_time
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Lấy các khung giờ đã đặt (theo start_time, end_time)
    $st = $pdo->prepare("
        SELECT start_time, end_time
        FROM bookings
        WHERE court_id = ? AND booking_date = ? AND status IN ('pending','approved')
    ");
    $st->execute([$court_id, $date]);
    $booked = $st->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Lọc các timeslot còn trống (so sánh theo thời gian)
    $available = array_filter($timeslots, function ($slot) use ($booked) {
        foreach ($booked as $b) {
            if ($b['start_time'] == $slot['start_time'] && $b['end_time'] == $slot['end_time']) {
                return false; // khung giờ này đã có người đặt
            }
        }
        return true;
    });

    echo json_encode(array_values($available), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
