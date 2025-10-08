<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // ✅ Truy vấn user theo số điện thoại
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Kiểm tra user tồn tại và mật khẩu đúng
    if ($user && password_verify($password, $user['password'])) {

        // ⚠️ Kiểm tra trạng thái tài khoản
        if ($user['status'] !== 'active') {
            echo "<div style='color:red;font-weight:bold;'>⚠️ Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.</div>";
            exit;
        }

        // ✅ Ghi thông tin vào SESSION (chỉ lưu dữ liệu cần thiết)
        $_SESSION['user'] = [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'] ?? '',
            'email'     => $user['email'] ?? '',
            'phone'     => $user['phone'] ?? '',
            'role'      => $user['role'] ?? 'user',
            'status'    => $user['status'] ?? 'active'
        ];

        // ✅ Nếu là admin, chuyển hướng tới dashboard admin
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        echo "<div style='color:red;font-weight:bold;'>❌ Sai số điện thoại hoặc mật khẩu!</div>";
    }
}
