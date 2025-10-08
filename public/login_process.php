<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // ✅ Truy vấn đúng cấu trúc bảng users
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Kiểm tra mật khẩu và trạng thái tài khoản
    if ($user && password_verify($password, $user['password'])) {

        if ($user['status'] !== 'active') {
            echo "⚠️ Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.";
            exit;
        }

        // ✅ Lưu đầy đủ thông tin vào session
        $_SESSION['user'] = [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'] ?? '',
            'email'     => $user['email'] ?? '',
            'phone'     => $user['phone'] ?? '',
            'role'      => $user['role'] ?? 'user',
            'status'    => $user['status'] ?? 'active'
        ];

        // ✅ Chuyển hướng dựa trên vai trò
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        echo "<div style='color:red; font-weight:bold;'>❌ Sai số điện thoại hoặc mật khẩu!</div>";
    }
}
