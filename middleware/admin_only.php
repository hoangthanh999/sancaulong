<?php
session_start();

// Nếu chưa đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit;
}

// Nếu không phải admin
if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}
