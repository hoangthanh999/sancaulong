<?php
// public/admin/manage_bookings.php
session_start();
require_once __DIR__ . '/../../config/db.php';

/* ==== CSRF đơn giản ==== */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===== Helpers ===== */
function hasColumn(PDO $pdo, $table, $col) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
  $st = $pdo->prepare($sql); $st->execute([$table, $col]);
  return (bool)$st->fetchColumn();
}
function hasTable(PDO $pdo, $table) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1";
  $st = $pdo->prepare($sql); $st->execute([$table]);
  return (bool)$st->fetchColumn();
}
function money($v){ return number_format((int)$v,0,',','.'); }

/* ===== Nhận diện cấu trúc DB ===== */
$has_total_price    = hasColumn($pdo,'bookings','total_price');
$has_deposit        = hasColumn($pdo,'bookings','deposit');
$has_deposit_status = hasColumn($pdo,'bookings','deposit_status');
$has_timeslot_id    = hasColumn($pdo,'bookings','timeslot_id');
$has_timeslot_text  = hasColumn($pdo,'bookings','timeslot');
$has_timeslots_tbl  = hasTable($pdo,'timeslots');

$has_full_name = hasColumn($pdo,'users','full_name');
$has_name      = hasColumn($pdo,'users','name');
$has_username  = hasColumn($pdo,'users','username');
$has_email     = hasColumn($pdo,'users','email');

$has_transactions_tbl = hasTable($pdo,'transactions');

/* ===== Tự tìm cột chứa “mã/ND chuyển khoản” ===== */
$transferCandidates = [
  'transfer_code','payment_code','payment_ref','payment_reference',
  'bank_note','bank_memo','transfer_message','payment_message',
  'transfer_note','note_transfer','deposit_code','payment_note'
];
$transferCol = null;
foreach ($transferCandidates as $c) {
  if (hasColumn($pdo,'bookings',$c)) { $transferCol = $c; break; }
}

/* ===== Build SELECT động ===== */
$fields = [
  "b.id","b.user_id","b.court_id","b.booking_date","b.status",
  "c.name AS court_name","c.price_per_hour"
];

// tên khách
$primaryUser = $has_full_name ? 'u.full_name' : ($has_name ? 'u.name' : ($has_username ? 'u.username' : 'NULL'));
$emailExpr   = $has_email ? 'u.email' : 'NULL';
$fields[] = "COALESCE($primaryUser, $emailExpr, CONCAT('Người dùng #',u.id)) AS customer_name";

// giá/tổng tiền/cọc
$fields[] = $has_total_price ? "b.total_price" : "c.price_per_hour AS total_price";
$fields[] = $has_deposit     ? "b.deposit"     : "0 AS deposit";
$fields[] = $has_deposit_status ? "b.deposit_status" : "'pending' AS deposit_status";

// timeslot
$join = "JOIN users u ON u.id=b.user_id JOIN courts c ON c.id=b.court_id";
$orderBy = "ORDER BY b.booking_date DESC, b.id DESC";

if ($has_timeslots_tbl && $has_timeslot_id) {
  $fields[] = "t.label AS slot_label";
  $fields[] = "t.start_time";
  $join .= " JOIN timeslots t ON t.id=b.timeslot_id";
  $orderBy = "ORDER BY b.booking_date DESC, t.start_time DESC";
} elseif ($has_timeslots_tbl && $has_timeslot_text) {
  $fields[] = "t.label AS slot_label";
  $fields[] = "t.start_time";
  $join .= " LEFT JOIN timeslots t ON t.label=b.timeslot";
  $orderBy = "ORDER BY b.booking_date DESC, t.start_time DESC";
} elseif ($has_timeslot_text) {
  $fields[] = "b.timeslot AS slot_label";
} else {
    $fields[] = "CONCAT(TIME_FORMAT(b.start_time, '%H:%i'), ' - ', TIME_FORMAT(b.end_time, '%H:%i')) AS slot_label";
  $orderBy = "ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC";
}

// cột mã CK
if ($transferCol) {
  $fields[] = "b.`$transferCol` AS transfer_text";
} else {
  $fields[] = "NULL AS transfer_text";
}

/* ===== Lọc nhanh: trạng thái / ngày / mã CK ===== */
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$date   = isset($_GET['date'])   ? trim($_GET['date'])   : '';
$code   = isset($_GET['code'])   ? trim($_GET['code'])   : '';

$where = "WHERE 1=1";
$params = [];
if ($status !== '') { $where .= " AND b.status = ?"; $params[] = $status; }
if ($date   !== '') { $where .= " AND b.booking_date = ?"; $params[] = $date; }
if ($code   !== '' && $transferCol) {
  $where .= " AND b.`$transferCol` LIKE ?";
  $params[] = "%$code%";
}

/* ===== Chuẩn hoá biểu thức deposit để dùng trong đối soát (KHÔNG dùng CASE) ===== */
$priceExpr   = $has_total_price ? "IFNULL(b.total_price, c.price_per_hour)" : "c.price_per_hour";
$depositExpr = $has_deposit ? "COALESCE(b.deposit, ROUND($priceExpr*0.15))"
                            : "ROUND($priceExpr*0.15)";

/* ===== Đối soát với bảng transactions (nếu có) ===== */
$matchSelect = "0 AS matched, NULL AS matched_txn_id, NULL AS matched_amount, NULL AS matched_paid_at";
if ($has_transactions_tbl && $transferCol) {
  $cond_tx  = "tx.reference LIKE CONCAT('%', b.`$transferCol`, '%')
               AND tx.amount BETWEEN GREATEST(0, ($depositExpr - 2000)) AND ($depositExpr + 2000)
               AND tx.paid_at BETWEEN DATE_SUB(b.booking_date, INTERVAL 3 DAY) AND DATE_ADD(b.booking_date, INTERVAL 3 DAY)";
  $cond_tx2 = str_replace('tx.', 'tx2.', $cond_tx);
  $cond_tx3 = str_replace('tx.', 'tx3.', $cond_tx);
  $cond_tx4 = str_replace('tx.', 'tx4.', $cond_tx);

  $matchSelect = "(SELECT IFNULL(MAX(1),0) FROM transactions tx WHERE $cond_tx LIMIT 1) AS matched,
                  (SELECT tx2.id      FROM transactions tx2 WHERE $cond_tx2 ORDER BY tx2.paid_at DESC LIMIT 1) AS matched_txn_id,
                  (SELECT tx3.amount  FROM transactions tx3 WHERE $cond_tx3 ORDER BY tx3.paid_at DESC LIMIT 1) AS matched_amount,
                  (SELECT tx4.paid_at FROM transactions tx4 WHERE $cond_tx4 ORDER BY tx4.paid_at DESC LIMIT 1) AS matched_paid_at";
}
$fields[] = $matchSelect;

/* ===== Thực thi ===== */
$sql = "SELECT ".implode(',', $fields)." 
        FROM bookings b
        $join
        $where
        ORDER BY b.booking_date DESC, b.id DESC
        LIMIT 200";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// UI
@include __DIR__ . '/header.php';
?>
<main class="container mt-4">
  <h2 class="mb-3">Quản lý đặt sân</h2>

  <?php if (isset($_GET['success']) && $_GET['success']=='1'): ?>
    <div class="alert alert-success">Đã xác nhận tiền cọc.</div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <form class="row g-2 mb-3" method="get" autocomplete="off">
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value="">-- Tất cả trạng thái --</option>
        <?php
          $opts = [
            'pending'=>'chờ duyệt (pending)',
            'approved'=>'đã duyệt (approved)',
            'rejected'=>'từ chối (rejected)',
            'cancelled'=>'đã hủy (cancelled)'
          ];
          foreach($opts as $v=>$label){
            $sel = ($status===$v)?'selected':'';
            echo "<option value=\"$v\" $sel>$label</option>";
          }
        ?>
      </select>
    </div>
    <div class="col-auto">
      <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>">
    </div>
    <div class="col-auto">
      <input type="text" name="code" class="form-control"
             placeholder="Lọc theo mã/ND chuyển khoản"
             value="<?php echo htmlspecialchars($code); ?>" <?php echo $transferCol? '' : 'disabled'; ?>>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">Lọc</button>
      <a class="btn btn-secondary" href="manage_bookings.php">Đặt lại</a>
    </div>
    <?php if (!$transferCol): ?>
      <div class="col-12"><small class="text-muted">Chưa thấy cột “mã/ND chuyển khoản” trong bảng <code>bookings</code>.</small></div>
    <?php endif; ?>
  </form>

  <?php if (empty($rows)): ?>
    <div class="alert alert-info">Không có đơn phù hợp.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Ngày</th>
            <th>Khung giờ</th>
            <th>Sân</th>
            <th>Khách</th>
            <th>Giá</th>
            <th>Cọc</th>
          
            <th>Mã CK</th>
            <th>Đối soát</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $i=>$r):
            $basePrice = isset($r['total_price']) && (int)$r['total_price']>0 ? (int)$r['total_price'] : (int)$r['price_per_hour'];
            $deposit = isset($r['deposit']) && (int)$r['deposit']>0 ? (int)$r['deposit'] : (int)round($basePrice*0.15);
            $slot = $r['slot_label'] ? $r['slot_label'] : '-';
            $depStatus = $r['deposit_status'] ?? 'pending';
            $transferText = $r['transfer_text'] ?? '';
            $matched = (int)($r['matched'] ?? 0) === 1;
          ?>
            <tr>
              <td><?php echo $i+1; ?></td>
              <td><?php echo date('d/m/Y', strtotime($r['booking_date'])); ?></td>
              <td><?php echo htmlspecialchars($slot); ?></td>
              <td><?php echo htmlspecialchars($r['court_name']); ?></td>
              <td><?php echo htmlspecialchars($r['customer_name']); ?></td>
              <td><?php echo money($basePrice); ?> VNĐ</td>
              <td><?php echo money($deposit); ?> VNĐ</td>
           
              <td><?php echo $transferText ? '<code>'.htmlspecialchars($transferText).'</code>' : '<span class="text-muted">—</span>'; ?></td>
              <td>
                <?php if ($transferText && $matched && $r['matched_paid_at']): ?>
                  <span class="badge bg-success" title="Khớp: <?php echo money($r['matched_amount']); ?> VND lúc <?php echo date('H:i d/m', strtotime($r['matched_paid_at'])); ?>">
                    Khớp giao dịch
                  </span>
                <?php elseif ($transferText && $has_transactions_tbl): ?>
                  <span class="badge bg-secondary" title="Chưa thấy giao dịch phù hợp (±3 ngày, dung sai 2.000đ)">Chưa thấy</span>
                <?php else: ?>
                  <span class="badge bg-light text-dark">Không khả dụng</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge
                  <?php
                   $stt = $r['status'];
echo (
  $stt==='approved' ? 'bg-success' :
  ($stt==='paid' ? 'bg-info' :
  ($stt==='pending' ? 'bg-warning text-dark' :
  ($stt==='rejected' ? 'bg-danger' : 'bg-secondary')))
);

                  ?>">
                  <?php
                    echo (
  $stt==='approved' ? 'đã duyệt' :
  ($stt==='paid' ? 'đã cọc' :
  ($stt==='pending' ? 'chờ duyệt' :
  ($stt==='rejected' ? 'từ chối' :
  ($stt==='cancelled' ? 'đã hủy' : $stt))))
);

                  ?>
                </span>
              </td>
              <td>
               <?php if ($r['status'] !== 'paid'): ?>

                  <form method="post" action="update_deposit.php"
                        onsubmit="return confirm('Xác nhận đánh dấu ĐÃ CỌC cho đơn #<?php echo (int)$r['id']; ?>?');"
                        style="display:inline;">
                    <input type="hidden" name="booking_id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" name="confirm_deposit"
                            class="btn <?php echo $matched ? 'btn-success' : 'btn-outline-secondary'; ?> btn-sm"
                            title="<?php echo $matched ? 'Đối soát đã khớp – bấm để xác nhận' : 'Chưa khớp, vẫn có thể xác nhận thủ công'; ?>">
                      Xác nhận đã cọc
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">Đã cọc</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php @include __DIR__ . '/footer.php'; ?>
