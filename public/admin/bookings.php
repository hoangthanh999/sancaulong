<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


// kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

// xử lý hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $st = $pdo->prepare("UPDATE bookings SET status='approved' WHERE id=?");
        $st->execute([$booking_id]);
    } elseif ($action === 'cancel') {
        $st = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?");
        $st->execute([$booking_id]);
    } elseif ($action === 'deposit_paid') {
        $st = $pdo->prepare("UPDATE bookings SET deposit_status='paid' WHERE id=?");
        $st->execute([$booking_id]);
    }

    header("Location: bookings.php");
    exit;
}

// lấy danh sách đơn đặt sân
$stmt = $pdo->query("SELECT b.id, u.name as user_name, c.name as court_name, 
                            b.booking_date, b.timeslot, b.status, 
                            b.total_price, b.deposit, b.deposit_status
                     FROM bookings b
                     JOIN users u ON b.user_id = u.id
                     JOIN courts c ON b.court_id = c.id
                     ORDER BY b.booking_date DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../../includes/admin_header.php'; ?>

<h3>📋 Quản lý đơn đặt sân</h3>

<table class="table table-bordered table-hover">
  <thead class="table-dark">
    <tr>
      <th>ID</th>
      <th>Người đặt</th>
      <th>Sân</th>
      <th>Ngày</th>
      <th>Khung giờ</th>
      <th>Trạng thái</th>
      <th>Tổng tiền</th>
      <th>Tiền cọc (15%)</th>
      <th>Trạng thái cọc</th>
      <th>Hành động</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?= $b['id'] ?></td>
        <td><?= htmlspecialchars($b['user_name']) ?></td>
        <td><?= htmlspecialchars($b['court_name']) ?></td>
        <td><?= $b['booking_date'] ?></td>
        <td><?= $b['timeslot'] ?></td>
        <td>
          <?php if ($b['status'] === 'pending'): ?>
            <span class="badge bg-warning">Chờ duyệt</span>
          <?php elseif ($b['status'] === 'approved'): ?>
            <span class="badge bg-success">Đã duyệt</span>
          <?php else: ?>
            <span class="badge bg-danger">Đã hủy</span>
          <?php endif; ?>
        </td>
        <td><?= number_format($b['total_price'], 0, ',', '.') ?> VNĐ</td>
        <td><?= number_format($b['deposit'], 0, ',', '.') ?> VNĐ</td>
        <td>
          <?php if ($b['deposit_status'] === 'pending'): ?>
            <span class="badge bg-secondary">Chưa cọc</span>
          <?php else: ?>
            <span class="badge bg-success">Đã cọc</span>
          <?php endif; ?>
        </td>
        <td>
          <!-- Nút xác nhận cọc -->
          <?php if ($b['deposit_status'] === 'pending'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <button type="submit" name="action" value="deposit_paid" class="btn btn-sm btn-info">Đã nhận cọc</button>
            </form>
          <?php endif; ?>

          <!-- Nút duyệt / hủy đơn -->
          <?php if ($b['status'] === 'pending'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Duyệt</button>
            </form>
            <form method="post" style="display:inline">
              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <button type="submit" name="action" value="cancel" class="btn btn-sm btn-danger">Hủy</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
