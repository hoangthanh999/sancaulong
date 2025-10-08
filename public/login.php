<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS Badminton - Đăng nhập</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="container">
        <div class="auth-card">
            <div class="logo">
                <h1>BS Badminton</h1>
                <p>Hệ thống quản lý sân cầu lông chuyên nghiệp</p>
            </div>

            <!-- Form Đăng nhập -->
            <form method="post" action="login_process.php" class="auth-form active">
                <h2>Đăng nhập</h2>
                <div class="input-group">
                    <label for="loginPhone">Số điện thoại</label>
                    <input type="tel" id="loginPhone" name="phone" placeholder="Nhập số điện thoại..." required>
                </div>
                <div class="input-group">
                    <label for="loginPassword">Mật khẩu</label>
                    <input type="password" id="loginPassword" name="password" placeholder="Nhập mật khẩu..." required>
                </div>
                <div class="forgot-password">
                    <a href="#">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn-primary">Đăng nhập</button>
                <p class="switch-form">
                    Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                </p>
            </form>
        </div>
    </div>

    <script src="assets/app.js"></script>
</body>
</html>
