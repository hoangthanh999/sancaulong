<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Chỉ cho admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $status = $_POST['status'];

    $update = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, is_admin=?, status=? WHERE id=?");
    $update->execute([$username, $full_name, $email, $is_admin, $status, $id]);

    header("Location: manage_users.php?success=1");
    exit;
}
?>

<?php include 'layout.php'; ?>

<h2 class="text-2xl font-bold text-gray-800 mb-6">Chỉnh sửa User</h2>

<form method="post" class="bg-white p-6 rounded-xl shadow-sm space-y-4">
  <div>
    <label class="block text-sm font-medium text-gray-700">Tên đăng nhập</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
           class="mt-1 w-full border px-3 py-2 rounded-lg" required>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Họ và tên</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>"
           class="mt-1 w-full border px-3 py-2 rounded-lg">
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
           class="mt-1 w-full border px-3 py-2 rounded-lg">
  </div>

  <div>
    <label class="inline-flex items-center">
      <input type="checkbox" name="is_admin" value="1" <?= $user['is_admin'] ? 'checked' : '' ?>>
      <span class="ml-2">Quản trị viên</span>
    </label>
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
    <select name="status" class="mt-1 w-full border px-3 py-2 rounded-lg">
      <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
      <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Ngưng</option>
    </select>
  </div>

  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    Lưu thay đổi
  </button>
</form>

<?php include 'footer.php'; ?>
