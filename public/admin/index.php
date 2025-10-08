<?php include '../../includes/header.php'; ?>

<h2 class="mb-4">📊 Bảng điều khiển Admin</h2>
<div class="row">
  <div class="col-md-4 mb-4">
    <div class="card card-custom text-center p-4 bg-primary text-white">
      <i class="fa-solid fa-building-columns fa-2x mb-3"></i>
      <h5>Quản lý sân</h5>
      <a href="courts.php" class="btn btn-light mt-2">Đi đến</a>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card card-custom text-center p-4 bg-success text-white">
      <i class="fa-solid fa-calendar-check fa-2x mb-3"></i>
      <h5>Đơn đặt sân</h5>
      <a href="bookings.php" class="btn btn-light mt-2">Đi đến</a>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card card-custom text-center p-4 bg-warning text-dark">
      <i class="fa-solid fa-users fa-2x mb-3"></i>
      <h5>Người dùng</h5>
      <a href="users.php" class="btn btn-dark mt-2">Đi đến</a>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
