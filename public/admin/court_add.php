<?php
require_once __DIR__ . '/../../config/db.php';
session_start();
include '../../includes/admin_header.php';

// Khi admin bấm nút Lưu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price_per_hour'];
    $status      = $_POST['status']; // 'active' hoặc 'inactive'

    if ($name === '' || $description === '' || $price <= 0) {
        echo "<div class='alert alert-danger'>⚠️ Vui lòng nhập đầy đủ thông tin hợp lệ.</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO courts (name, description, price_per_hour, status, created_at)
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $description, $price, $status]);

        header("Location: courts.php");
        exit;
    }
}
?>

<h2>➕ Thêm sân mới</h2>

<form method="post" class="card p-4 shadow-sm bg-light">
  <div class="mb-3">
    <label class="form-label">Tên sân</label>
    <input type="text" name="name" class="form-control" placeholder="Nhập tên sân..." required>
  </div>

  <div class="mb-3">
    <label class="form-label">Mô tả</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả sân..." required></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Giá mỗi giờ (VNĐ)</label>
    <input type="number" name="price_per_hour" class="form-control" placeholder="Ví dụ: 100000" min="0" step="1000" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="active" selected>Hoạt động</option>
      <option value="inactive">Ẩn</option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">💾 Lưu</button>
  <a href="courts.php" class="btn btn-secondary">⬅ Quay lại</a>
</form>
