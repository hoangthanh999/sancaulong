<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 🔍 Lấy user theo email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Kiểm tra tồn tại và mật khẩu đúng
    if ($user && password_verify($password, $user['password'])) {

        // ❗ Kiểm tra trạng thái tài khoản
        if ($user['status'] !== 'active') {
            $error = "⚠️ Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.";
        }
        // ❗ Kiểm tra quyền admin
        elseif ($user['role'] !== 'admin') {
            $error = "🚫 Tài khoản này không có quyền truy cập trang quản trị.";
        }
        else {
            // ✅ Lưu session an toàn
            $_SESSION['user'] = [
                'id'        => $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'] ?? '',
                'email'     => $user['email'] ?? '',
                'phone'     => $user['phone'] ?? '',
                'role'      => $user['role'],
                'status'    => $user['status']
            ];

            // ✅ Chuyển hướng đến dashboard admin
            header("Location: index.php");
            exit;
        }

    } else {
        $error = "❌ Sai email hoặc mật khẩu!";
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card card-custom p-4">
      <h3 class="text-center mb-4">🔑 Đăng nhập Quản trị viên</h3>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="Nhập email admin..." required>
        </div>
        <div class="mb-3">
          <label>Mật khẩu</label>
          <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu..." required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
