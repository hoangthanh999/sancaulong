<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏸 Badminton Booking</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f6f9;
    }
    .navbar {
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .hero {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      border-radius: 12px;
      padding: 60px 30px;
      text-align: center;
      margin-bottom: 30px;
    }
    .card-custom {
      border: none;
      border-radius: 12px;
      transition: 0.3s;
    }
    .card-custom:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    footer {
      margin-top: 50px;
      background: #212529;
      color: #aaa;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-shuttlecock"></i> Badminton Booking</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
        <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-2 px-3" href="register.php">Đăng ký</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
