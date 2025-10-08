<?php
// public/booking.php — phiên bản hỗ trợ đặt giờ linh hoạt (custom start-end time)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = ['id' => 1, 'full_name' => 'Demo User'];
    }
    $user = $_SESSION['user'];

    // ✅ Lấy danh sách sân
    $courts = $pdo->query("
        SELECT id, name, price_per_hour 
        FROM courts 
        WHERE status = 'active' 
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    echo "<pre style='color:#fff;background:#900;padding:12px;'>DB ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

require_once __DIR__ . '/partials/header.php';
?>

<main class="main-container">
  <section class="welcome-section">
    <div class="welcome-text">
      <h1>📝 Đặt sân linh hoạt</h1>
      <p>Bạn có thể chọn giờ bất kỳ (ví dụ: 12:15 đến 13:45).</p>
    </div>
  </section>

  <div class="container mt-4" style="max-width:720px;">
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="alert alert-success"><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>

    <form method="post" action="booking_process.php" class="card p-4 shadow-sm bg-dark text-white" id="bookingForm">
      <div class="mb-3">
        <label class="form-label">Chọn sân</label>
        <select name="court_id" id="court_id" class="form-select" required>
          <option value="">-- Chọn sân --</option>
          <?php foreach ($courts as $c): ?>
            <option value="<?= (int)$c['id']; ?>">
              <?= htmlspecialchars($c['name']); ?> (<?= number_format((int)$c['price_per_hour'], 0, ',', '.'); ?> VNĐ/giờ)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Ngày</label>
        <input 
          type="date" 
          name="booking_date" 
          id="booking_date" 
          class="form-control" 
          required 
          min="<?= date('Y-m-d'); ?>">
      </div>

      <!-- ✅ Giờ bắt đầu -->
      <div class="mb-3">
        <label class="form-label">Giờ bắt đầu</label>
        <input 
          type="time" 
          name="start_time" 
          id="start_time" 
          class="form-control" 
          required 
          step="900"> <!-- bước nhảy 15 phút -->
      </div>

      <!-- ✅ Giờ kết thúc -->
      <div class="mb-3">
        <label class="form-label">Giờ kết thúc</label>
        <input 
          type="time" 
          name="end_time" 
          id="end_time" 
          class="form-control" 
          required 
          step="900">
      </div>

      <div class="mb-3">
        <label class="form-label">Ghi chú (nếu có)</label>
        <textarea class="form-control" name="notes" rows="2" placeholder="Ví dụ: mượn cầu, thêm người..."></textarea>
      </div>

      <div class="mb-3">
        <strong>💰 Tổng tiền dự kiến: <span id="previewPrice">0</span> VNĐ</strong><br>
        <small class="text-muted">(Tính tự động theo số giờ × giá sân)</small>
      </div>

      <button type="submit" class="btn btn-primary">Đặt sân</button>
    </form>
  </div>
</main>

<script>
// ✅ Tự tính tiền theo giờ nhập
const priceMap = {};
<?php foreach ($courts as $c): ?>
  priceMap[<?= (int)$c['id']; ?>] = <?= (int)$c['price_per_hour']; ?>;
<?php endforeach; ?>

function updatePrice() {
  const courtId = document.getElementById('court_id').value;
  const start = document.getElementById('start_time').value;
  const end = document.getElementById('end_time').value;
  const priceEl = document.getElementById('previewPrice');
  if (!courtId || !start || !end) {
    priceEl.textContent = '0';
    return;
  }

  const pricePerHour = priceMap[courtId] || 0;
  const startMinutes = toMinutes(start);
  const endMinutes = toMinutes(end);
  if (endMinutes <= startMinutes) {
    priceEl.textContent = '0';
    return;
  }
  const hours = (endMinutes - startMinutes) / 60;
  const total = Math.round(hours * pricePerHour);
  priceEl.textContent = total.toLocaleString('vi-VN');
}

function toMinutes(timeStr) {
  const [h, m] = timeStr.split(':').map(Number);
  return h * 60 + m;
}

['court_id', 'start_time', 'end_time'].forEach(id => {
  document.getElementById(id).addEventListener('change', updatePrice);
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
