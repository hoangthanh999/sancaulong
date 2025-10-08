<?php
require_once __DIR__ . '/../../config/db.php';
session_start();
include '../../includes/admin_header.php';

$id = $_GET['id'] ?? 0;

// ✅ Lấy thông tin sân theo ID
$stmt = $pdo->prepare("SELECT * FROM courts WHERE id=?");
$stmt->execute([$id]);
$court = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$court) {
    echo "<div class='alert alert-danger'>❌ Sân không tồn tại.</div>";
    include '../../includes/admin_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price_per_hour'];
    $status      = $_POST['status']; // 'active' hoặc 'inactive'

    // ✅ Cập nhật vào DB
    $stmt = $pdo->prepare("UPDATE courts SET name=?, description=?, price_per_hour=?, status=? WHERE id=?");
    $stmt->execute([$name, $description, $price, $status, $id]);

    // ✅ Quay lại danh sách
    header("Location: courts.php");
    exit;
}
?>

<h2>✏️ Sửa thông tin sân</h2>

<form method="post" class="card p-4 shadow-sm bg-light">
  <div class="mb-3">
    <label class="form-label">Tên sân</label>
    <input type="text" name="name" class="form-control" 
           value="<?= htmlspecialchars($court['name']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Mô tả</label>
    <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($court['description']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Giá mỗi giờ (VNĐ)</label>
    <input type="number" name="price_per_hour" class="form-control" 
           value="<?= htmlspecialchars($court['price_per_hour']) ?>" min="0" step="1000" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="active" <?= $court['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
      <option value="inactive" <?= $court['status'] === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
  <a href="courts.php" class="btn btn-secondary">⬅ Quay lại</a>
</form>

<?php include '../../includes/admin_footer.php'; ?>
