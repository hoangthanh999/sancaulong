<?php
require_once __DIR__ . '/../config/db.php';
include '../includes/header.php';

$stmt = $pdo->query("SELECT * FROM courts WHERE active=1 ORDER BY name ASC");
$courts = $stmt->fetchAll();
?>

<h2 class="mb-4">🏸 Danh sách sân cầu lông</h2>
<div class="row">
  <?php foreach($courts as $c): ?>
    <div class="col-md-4 mb-4">
      <div class="card card-custom h-100">
        <?php if ($c['image']): ?>
          <img src="../uploads/<?= $c['image'] ?>" class="card-img-top" style="height:200px;object-fit:cover">
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($c['name']) ?></h5>
          <p class="card-text">Giá: <strong><?= number_format($c['price_per_hour'],0,',','.') ?> đ/giờ</strong></p>
          <a href="booking.php?court_id=<?= $c['id'] ?>" class="btn btn-primary w-100">Đặt ngay</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
