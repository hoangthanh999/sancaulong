<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!$id || !$action) {
    header("Location: users.php");
    exit;
}

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['flash_error'] = "❌ Người dùng không tồn tại.";
    header("Location: users.php");
    exit;
}

switch ($action) {
    case 'make_admin':
        $pdo->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = "✅ Đã nâng tài khoản {$user['username']} lên Admin.";
        break;

    case 'make_user':
        $pdo->prepare("UPDATE users SET role='user' WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = "✅ Đã hạ quyền tài khoản {$user['username']} xuống User.";
        break;

    case 'deactivate':
        $pdo->prepare("UPDATE users SET status='inactive' WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = "🔒 Đã khoá tài khoản {$user['username']}.";
        break;

    case 'activate':
        $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = "✅ Đã mở khoá tài khoản {$user['username']}.";
        break;

    case 'reset_password':
        if (empty($user['phone'])) {
            $_SESSION['flash_error'] = "⚠️ Không thể cấp lại mật khẩu vì người dùng chưa có số điện thoại.";
        } else {
            $newPasswordHash = password_hash($user['phone'], PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newPasswordHash, $id]);
            $_SESSION['flash_success'] = "🔑 Đã cấp lại mật khẩu mặc định cho <b>{$user['username']}</b> (mật khẩu = số điện thoại).";
        }
        break;
}

header("Location: users.php");
exit;
