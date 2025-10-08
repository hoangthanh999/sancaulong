<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Kết nối DB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirm   = trim($_POST['confirmPassword'] ?? '');

    // 1️⃣ Kiểm tra dữ liệu rỗng
    if ($full_name === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        die("⚠️ Vui lòng nhập đầy đủ thông tin!");
    }

    // 2️⃣ Kiểm tra mật khẩu khớp
    if ($password !== $confirm) {
        die("⚠️ Mật khẩu không khớp!");
    }

    // 3️⃣ Kiểm tra email hoặc số điện thoại đã tồn tại
    $check = $pdo->prepare("SELECT id FROM users WHERE phone = ? OR email = ?");
    $check->execute([$phone, $email]);

    if ($check->fetch()) {
        die("⚠️ Email hoặc số điện thoại đã được đăng ký, vui lòng dùng thông tin khác!");
    }

    // 4️⃣ Hash mật khẩu
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 5️⃣ Tạo username tự động
    $username = 'user_' . substr(md5(time() . $phone), 0, 6);

    // 6️⃣ Thêm vào DB
    $insert = $pdo->prepare("
        INSERT INTO users (username, password, email, full_name, phone, role, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'user', 'active', NOW())
    ");

    $ok = $insert->execute([$username, $hashedPassword, $email, $full_name, $phone]);

    // 7️⃣ Nếu thành công -> lưu session và chuyển hướng
    if ($ok) {
        $_SESSION['user'] = [
            'id'        => $pdo->lastInsertId(),
            'full_name' => $full_name,
            'email'     => $email,
            'phone'     => $phone,
            'role'      => 'user'
        ];

        header("Location: index.php");
        exit;
    } else {
        die("❌ Lỗi khi tạo tài khoản, vui lòng thử lại!");
    }
} else {
    header("Location: register.php");
    exit;
}
?>
