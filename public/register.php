<?php
session_start();

// Nếu đã đăng nhập thì về trang chủ
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
    <title>BS Badminton - Đăng ký</title>

    <!-- CSS dự án -->
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.0">
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="container">
        <div class="auth-card">
            <div class="logo">
                <h1>🏸 BS Badminton</h1>
                <p>Tạo tài khoản để bắt đầu đặt sân</p>
            </div>

            <!-- 📝 Form Đăng ký -->
            <form method="post" action="register_process.php" class="auth-form active" autocomplete="off">
                <h2>Đăng ký tài khoản</h2>

                <!-- Họ và tên -->
                <div class="input-group">
                    <label for="registerName">Họ và tên</label>
                    <input 
                        type="text" 
                        id="registerName" 
                        name="name" 
                        placeholder="Nhập họ tên đầy đủ..." 
                        required>
                </div>

                <!-- Email -->
                <div class="input-group">
                    <label for="registerEmail">Email</label>
                    <input 
                        type="email" 
                        id="registerEmail" 
                        name="email" 
                        placeholder="Nhập địa chỉ email..." 
                        required>
                </div>

                <!-- Số điện thoại -->
                <div class="input-group">
                    <label for="registerPhone">Số điện thoại</label>
                    <input 
                        type="tel" 
                        id="registerPhone" 
                        name="phone" 
                        pattern="[0-9]{9,11}" 
                        placeholder="Nhập số điện thoại..." 
                        required>
                </div>

                <!-- Mật khẩu -->
                <div class="input-group">
                    <label for="registerPassword">Mật khẩu</label>
                    <input 
                        type="password" 
                        id="registerPassword" 
                        name="password" 
                        minlength="6"
                        placeholder="Tạo mật khẩu mạnh..." 
                        required>
                </div>

                <!-- Xác nhận mật khẩu -->
                <div class="input-group">
                    <label for="confirmPassword">Xác nhận mật khẩu</label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword" 
                        minlength="6"
                        placeholder="Nhập lại mật khẩu..." 
                        required>
                </div>

                <!-- Điều khoản -->
                <div class="checkbox-group">
                    <input type="checkbox" id="agreeTerms" required>
                    <label for="agreeTerms">
                        Tôi đồng ý với 
                        <a href="#">Điều khoản sử dụng</a> 
                        và 
                        <a href="#">Chính sách bảo mật</a>
                    </label>
                </div>

                <!-- Nút đăng ký -->
                <button type="submit" class="btn-primary">Tạo tài khoản</button>

                <p class="switch-form">
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                </p>
            </form>
        </div>
    </div>

    <!-- JS hiệu ứng -->
    <script src="assets/js/app.js?v=1.0.0"></script>
</body>
</html>
