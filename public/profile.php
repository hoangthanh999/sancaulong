<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $email     = trim($_POST['email']);

    if ($full_name === '' || $phone === '' || $email === '') {
        $error = "⚠️ Vui lòng nhập đầy đủ thông tin.";
    } else {
        // ✅ Cập nhật thông tin user
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, phone=?, email=? WHERE id=?");
        $stmt->execute([$full_name, $phone, $email, $user['id']]);

        // ✅ Cập nhật lại session
        $_SESSION['user']['full_name'] = $full_name;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['email'] = $email;

        $success = "✅ Cập nhật thông tin thành công!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>BS Badminton - Hồ sơ cá nhân</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/all.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="card shadow-sm p-4">
    <h3 class="mb-3"><i class="fa-solid fa-user"></i> Hồ sơ cá nhân</h3>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Tên đăng nhập</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>

      </div>

      <div class="mb-3">
        <label class="form-label">Họ và tên</label>
        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Số điện thoại</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Quyền hạn</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars(strtoupper($user['role'])) ?>" disabled>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-save"></i> Lưu thay đổi
      </button>
      <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </form>
  </div>
</div>

</body>
</html>
