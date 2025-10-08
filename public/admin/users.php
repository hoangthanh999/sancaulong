<?php  
require_once __DIR__ . '/../../config/db.php';
include '../../includes/header.php';

// Lấy danh sách người dùng
$stmt = $pdo->query("SELECT id, username, full_name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-4">👤 Quản lý người dùng</h2>

<table class="table table-bordered table-hover align-middle">
  <thead class="table-dark text-center">
    <tr>
      <th>ID</th>
      <th>Tên đăng nhập</th>
      <th>Họ và tên</th>
      <th>Email</th>
      <th>Số điện thoại</th>
      <th>Vai trò</th>
      <th>Trạng thái</th>
      <th>Ngày tạo</th>
      <th>Hành động</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr class="text-center">
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
        <td>
          <?php if ($u['role'] === 'admin'): ?>
            <span class="badge bg-primary">Admin</span>
          <?php else: ?>
            <span class="badge bg-secondary">User</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($u['status'] === 'active'): ?>
            <span class="badge bg-success">Hoạt động</span>
          <?php else: ?>
            <span class="badge bg-danger">Khoá</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($u['created_at']) ?></td>
        <td>
          <!-- Nâng / Hạ quyền -->
          <?php if ($u['role'] !== 'admin'): ?>
            <a href="user_action.php?id=<?= $u['id'] ?>&action=make_admin" class="btn btn-sm btn-success">Nâng Admin</a>
          <?php else: ?>
            <a href="user_action.php?id=<?= $u['id'] ?>&action=make_user" class="btn btn-sm btn-warning">Hạ User</a>
          <?php endif; ?>

          <!-- Khoá / Mở khoá -->
          <?php if ($u['status'] === 'active'): ?>
            <a href="user_action.php?id=<?= $u['id'] ?>&action=deactivate" class="btn btn-sm btn-danger"
               onclick="return confirm('Khoá tài khoản này?')">Khoá</a>
          <?php else: ?>
            <a href="user_action.php?id=<?= $u['id'] ?>&action=activate" class="btn btn-sm btn-info">Mở khoá</a>
          <?php endif; ?>

          <!-- Cấp lại mật khẩu -->
          <a href="user_action.php?id=<?= $u['id'] ?>&action=reset_password" 
             class="btn btn-sm btn-outline-primary"
             onclick="return confirm('Cấp lại mật khẩu mặc định là số điện thoại của người dùng?')">
             🔑 Cấp lại mật khẩu
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
