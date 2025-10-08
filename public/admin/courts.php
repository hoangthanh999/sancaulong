<?php
require_once __DIR__ . '/../../config/db.php';
session_start();
include '../../includes/admin_header.php';

// Lấy danh sách sân
$stmt = $pdo->query("SELECT * FROM courts ORDER BY id DESC");
$courts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>⚙️ Quản lý sân cầu lông</h2>
  <a href="court_add.php" class="btn btn-success">➕ Thêm sân</a>
</div>

<table class="table table-bordered table-striped align-middle">
  <thead class="table-dark">
    <tr>
      <th width="5%">ID</th>
      <th width="20%">Tên sân</th>
      <th width="35%">Mô tả</th>
      <th width="15%">Giá/giờ</th>
      <th width="10%">Trạng thái</th>
      <th width="15%">Hành động</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($courts)): ?>
      <tr>
        <td colspan="6" class="text-center text-muted">Chưa có sân nào được tạo.</td>
      </tr>
    <?php else: ?>
      <?php foreach ($courts as $c): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
          <td><?= nl2br(htmlspecialchars($c['description'])) ?></td>
          <td><?= number_format($c['price_per_hour'], 0, ',', '.') ?> đ</td>
          <td>
            <?php if ($c['status'] === 'active'): ?>
              <span class="badge bg-success">Hoạt động</span>
            <?php else: ?>
              <span class="badge bg-secondary">Ẩn</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="court_edit.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">
              ✏️ Sửa
            </a>
            <a href="court_delete.php?id=<?= $c['id'] ?>" 
               class="btn btn-danger btn-sm"
               onclick="return confirm('Xóa sân này?')">
              🗑️ Xóa
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php include '../../includes/admin_footer.php'; ?>
