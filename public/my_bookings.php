<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
$user_id = (int)$_SESSION['user']['id'];

function hasColumn(PDO $pdo, $table, $col) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
  $st = $pdo->prepare($sql); $st->execute([$table, $col]);
  return (bool)$st->fetchColumn();
}
function hasTable(PDO $pdo, $table) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1";
  $st = $pdo->prepare($sql); $st->execute([$table]);
  return (bool)$st->fetchColumn();
}

$has_total_price    = hasColumn($pdo, 'bookings', 'total_price');
$has_deposit        = hasColumn($pdo, 'bookings', 'deposit');
$has_deposit_status = hasColumn($pdo, 'bookings', 'deposit_status');
$has_notes          = hasColumn($pdo, 'bookings', 'notes');
$has_created_at     = hasColumn($pdo, 'bookings', 'created_at');
$has_timeslot_id    = hasColumn($pdo, 'bookings', 'timeslot_id');
$has_timeslot_text  = hasColumn($pdo, 'bookings', 'timeslot');
$has_timeslots_tbl  = hasTable($pdo, 'timeslots');

$fields = array(
  "b.id",
  "b.booking_date",
  "b.status",
  "c.name AS court_name",
  "c.price_per_hour"
);
$fields[] = $has_total_price    ? "b.total_price"    : "0 AS total_price";
$fields[] = $has_deposit        ? "b.deposit"        : "0 AS deposit";
$fields[] = $has_deposit_status ? "b.deposit_status" : "'pending' AS deposit_status";
$fields[] = $has_notes          ? "b.notes"          : "NULL AS notes";
$fields[] = $has_created_at     ? "b.created_at"     : "NULL AS created_at";

$join = "JOIN courts c ON c.id = b.court_id";
$orderBy = "ORDER BY b.booking_date DESC, b.id DESC";

if ($has_timeslots_tbl && $has_timeslot_id) {
  $fields[] = "t.label AS slot_label";
  $fields[] = "t.start_time";
  $join .= " JOIN timeslots t ON t.id = b.timeslot_id";
  $orderBy = "ORDER BY b.booking_date DESC, t.start_time DESC";
} elseif ($has_timeslots_tbl && $has_timeslot_text) {
  $fields[] = "t.label AS slot_label";
  $fields[] = "t.start_time";
  $join .= " LEFT JOIN timeslots t ON t.label = b.timeslot";
  $orderBy = "ORDER BY b.booking_date DESC, t.start_time DESC";
} elseif ($has_timeslot_text) {
  $fields[] = "b.timeslot AS slot_label";
} else {
  // ✅ Nếu không có cột timeslot, tự ghép giờ từ start_time + end_time
  $fields[] = "CONCAT(DATE_FORMAT(b.start_time, '%H:%i'), ' - ', DATE_FORMAT(b.end_time, '%H:%i')) AS slot_label";
}


$sql = "SELECT " . implode(", ", $fields) . " FROM bookings b $join WHERE b.user_id = ? $orderBy";
$st = $pdo->prepare($sql);
$st->execute([$user_id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

@include __DIR__ . '/partials/header.php';
?>
<main class="container mt-4">
  <h2 class="mb-3">Đơn đặt sân của tôi</h2>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">Chưa có đơn đặt nào.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Ngày</th>
            <th>Khung giờ</th>
            <th>Sân</th>
            <th>Giá</th>
            <th>Cọc (15%)</th>
            <th>Trạng thái</th>
            <th class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r):
            $basePrice = (int)(isset($r['total_price']) ? $r['total_price'] : 0);
            if ($basePrice <= 0) $basePrice = (int)$r['price_per_hour'];

            $dep = (int)(isset($r['deposit']) ? $r['deposit'] : 0);
            if ($dep <= 0) $dep = (int)round($basePrice * 0.15);

            $slotLabel = isset($r['slot_label']) ? $r['slot_label'] : '-';

            $status = $r['status'];
$badgeClass = 'bg-light text-dark';
switch ($status) {
  case 'approved':
    $badgeClass = 'bg-success';
    $statusText = 'Đã duyệt';
    break;
  case 'paid':
    $badgeClass = 'bg-info text-dark';
    $statusText = 'Đã cọc';
    break;
  case 'pending':
    $badgeClass = 'bg-warning text-dark';
    $statusText = 'Chờ duyệt';
    break;
  case 'cancelled':
    $badgeClass = 'bg-secondary';
    $statusText = 'Đã hủy';
    break;
  case 'rejected':
    $badgeClass = 'bg-danger';
    $statusText = 'Từ chối';
    break;
  default:
    $statusText = ucfirst($status);
}


            $depStatus = isset($r['deposit_status']) ? $r['deposit_status'] : 'pending';
          ?>
            <tr>
              <td><?php echo $i+1; ?></td>
              <td><?php echo date('d/m/Y', strtotime($r['booking_date'])); ?></td>
              <td><?php echo htmlspecialchars($slotLabel); ?></td>
              <td><?php echo htmlspecialchars($r['court_name']); ?></td>
              <td><?php echo number_format($basePrice,0,',','.'); ?> VNĐ</td>
              <td>
                <?php echo number_format($dep,0,',','.'); ?> VNĐ
                <?php if ($has_deposit_status): ?>
                  <span class="badge <?php echo ($depStatus === 'paid') ? 'bg-success' : 'bg-secondary'; ?>">
                    <?php echo htmlspecialchars($depStatus); ?>
                  </span>
                <?php endif; ?>
              </td>
             <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($statusText); ?></span></td>

              <td class="text-end">
                <?php if ($status === 'pending'): ?>
                  <a class="btn btn-sm btn-success me-2" href="payment.php?booking_id=<?php echo (int)$r['id']; ?>">Thanh toán cọc</a>
                  <a class="btn btn-sm btn-outline-danger" href="cancel_booking.php?id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Hủy đơn này?');">Hủy</a>
                <?php else: ?>
                  <a class="btn btn-sm btn-outline-light" href="payment.php?booking_id=<?php echo (int)$r['id']; ?>">Xem QR</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php @include __DIR__ . '/partials/footer.php'; ?>
