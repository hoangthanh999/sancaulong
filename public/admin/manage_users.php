<?php
include 'layout.php'; // header & kết nối $pdo

// Lấy danh sách user
$stmt = $pdo->query("SELECT id, username, full_name, email, phone, role, status, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6">👤 Quản lý người dùng</h2>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên đăng nhập</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Họ và tên</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số điện thoại</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vai trò</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày tạo</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hành động</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($users as $u): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($u['username']) ?></td>
            <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
            <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
            <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium 
                <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                <?= $u['role'] === 'admin' ? 'Admin' : 'User' ?>
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium 
                <?= $u['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= $u['status'] === 'active' ? 'Hoạt động' : 'Ngưng' ?>
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($u['created_at']) ?></td>
            <td class="px-6 py-4 text-sm text-gray-500">
              <div class="flex space-x-2">
                <!-- Sửa thông tin -->
                <a href="user_edit.php?id=<?= $u['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Chỉnh sửa">
                  ✏️
                </a>
                <!-- Bật / tắt tài khoản -->
                <a href="user_toggle.php?id=<?= $u['id'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Đổi trạng thái">
                  🔄
                </a>
                <!-- Reset mật khẩu -->
                <a href="user_resetpw.php?id=<?= $u['id'] ?>" 
                   onclick="return confirm('Cấp lại mật khẩu mặc định là số điện thoại?')" 
                   class="text-indigo-600 hover:text-indigo-800" title="Cấp lại mật khẩu">
                  🔐
                </a>
                <!-- Xóa -->
                <a href="user_delete.php?id=<?= $u['id'] ?>" 
                   onclick="return confirm('Xóa user này?')" 
                   class="text-red-600 hover:text-red-800" title="Xóa">
                  🗑
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
