<?php
// public/payment.php
// public/payment.php
session_start();
require_once __DIR__ . '/../config/db.php';

// ⚙️ Nạp cấu hình ứng dụng
$app = require __DIR__ . '/../config/app.php';

/* ========= Helpers ========= */
function get_setting(array $app, $key, $default = null) {
  // Ưu tiên đọc từ app.php
  return $app[$key] ?? $default;
}
function hasColumn(PDO $pdo,$t,$c){
  $st=$pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1");
  $st->execute([$t,$c]);
  return (bool)$st->fetchColumn();
}
function money($v){ return number_format((int)$v,0,',','.'); }

/* ========= Input ========= */
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($booking_id <= 0) { header("Location: my_bookings.php"); exit; }

/* ========= Kiểm tra cấu trúc bảng ========= */
$has_timeslot_id    = hasColumn($pdo,'bookings','timeslot_id');
$has_timeslot_text  = hasColumn($pdo,'bookings','timeslot');
$has_total_price    = hasColumn($pdo,'bookings','total_price');
$has_deposit_col    = hasColumn($pdo,'bookings','deposit');
$has_dep_status_col = hasColumn($pdo,'bookings','deposit_status');
$has_transfer_code  = hasColumn($pdo,'bookings','transfer_code');

/* ========= Lấy đơn ========= */
$fields = [
  "b.id","b.user_id","b.court_id","b.booking_date","b.status",
  "c.name AS court_name","c.price_per_hour"
];
if ($has_total_price)   $fields[] = "b.total_price";
if ($has_deposit_col)   $fields[] = "b.deposit";
if ($has_dep_status_col)$fields[] = "b.deposit_status";
if ($has_transfer_code) $fields[] = "b.transfer_code";

$join = "JOIN courts c ON c.id=b.court_id";
if ($has_timeslot_id) {
  $fields[]="t.label AS slot_label";
  $join.=" JOIN timeslots t ON t.id=b.timeslot_id";
} elseif ($has_timeslot_text) {
  $fields[]="b.timeslot AS slot_label";
} else {
  // ✅ Nếu không có 2 cột kia, tự tạo slot_label từ start_time + end_time
  $fields[]="CONCAT(DATE_FORMAT(b.start_time, '%H:%i'), ' - ', DATE_FORMAT(b.end_time, '%H:%i')) AS slot_label";
}

$sql = "SELECT ".implode(',', $fields)." FROM bookings b $join WHERE b.id=? LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$booking_id]);
$bk = $st->fetch(PDO::FETCH_ASSOC);
if (!$bk) { echo "Đơn không tồn tại."; exit; }

/* ========= Tính giá & tiền cọc ========= */
$price = isset($bk['total_price']) && (int)$bk['total_price']>0
        ? (int)$bk['total_price']
        : (int)$bk['price_per_hour'];

$depositRate = (float)($app['deposit_rate'] ?? 0.15);
$percent = (int)round($depositRate * 100);
$deposit = (int)round($price * $depositRate);

/* ========= Cấu hình VietQR ========= */
$bank = $app['bank'] ?? [];
$bankCode   = trim($bank['bank_code'] ?? 'MB');
$accountNo  = preg_replace('/\D+/','', $bank['account_no'] ?? '');
$accountNm  = trim($bank['account_name'] ?? '');
$template   = trim($bank['qr_template'] ?? 'compact');

/* ========= Mapping tên ngân hàng → BIN ========= */
$map = [
  'vietcombank'=> '970436','vcb'=>'970436',
  'mbbank'=> '970422','mb'=>'970422',
  'techcombank'=> '970407','tcb'=>'970407',
  'tpbank'=>'970423','bidv'=>'970418',
  'vietinbank'=>'970415','agribank'=>'970405',
  'acb'=>'970416','sacombank'=>'970403','vib'=>'970441'
];
$bankKey = strtolower(preg_replace('/\s+/','',$bankCode));
if (!preg_match('/^\d{6}$/',$bankCode) && isset($map[$bankKey])) {
  $bankCode = $map[$bankKey];
}

/* ========= Nội dung chuyển khoản ========= */
$memo = sprintf('BSB-%06d', $booking_id);

/* ========= Tạo URL ảnh QR ========= */
$qrUrl = "https://img.vietqr.io/image/{$bankCode}-{$accountNo}-{$template}.png"
        ."?amount={$deposit}&addInfo=".urlencode($memo)
        ."&accountName=".urlencode($accountNm);

/* ========= Giao diện ========= */
include __DIR__ . '/partials/header.php';
?>
<!-- Giữ nguyên phần HTML hiển thị QR của bạn -->

<style>
  .pay-wrap{max-width: 980px; margin:40px auto; display:grid; grid-template-columns:1.2fr 1fr; gap:28px; align-items:start}
  .card-dark{background:#1f2937; color:#e5e7eb; padding:20px 22px; border-radius:12px; position:relative}
  .badge-q{position:absolute; top:10px; right:12px; opacity:.85}
  .muted{color:#93a3b8; font-size:14px}
  .copy-btn{cursor:pointer; margin-left:10px; font-size:12px; padding:2px 8px; border:1px solid #374151; background:#111827; color:#e5e7eb; border-radius:6px}
  .notice{margin-top:10px; color:#fbbf24}
  .btn-floating{position:fixed; right:28px; top:50%; transform:translateY(-50%); background:#6b7280; color:#fff; border:0; border-radius:10px; padding:10px 12px}
  .qr-box{display:flex; align-items:center; justify-content:center; background:#111827; border-radius:10px; padding:10px; min-height:260px}
  img.qr{max-width:260px; width:100%; height:auto}
  @media (max-width: 900px){ .pay-wrap{grid-template-columns:1fr; } .btn-floating{top:auto; bottom:20px; transform:none;} }
</style>

<main class="container">
  <div class="pay-wrap">
    <div>
      <h2 style="color:#fff; font-weight:700; margin:12px 0 18px">Thanh toán cọc đơn #<?php echo $booking_id; ?></h2>
      <div class="card-dark">
        <img class="badge-q" src="https://img.vietqr.io/image/mbbank-000000-qr_only.png" width="64" height="64" alt="VietQR">
        <p><b>Sân:</b> <?php echo htmlspecialchars($bk['court_name']); ?></p>
        <p><b>Ngày:</b> <?php echo date('d/m/Y', strtotime($bk['booking_date'])); ?></p>
        <p><b>Khung giờ:</b> <?php echo htmlspecialchars($bk['slot_label'] ?? '-'); ?></p>
        <p><b>Giá/giờ:</b> <?php echo money($price); ?> VNĐ</p>
        <p><b>Tổng tiền:</b> <?php echo money($price); ?> VNĐ</p>
        <p><b>Cọc (<?php echo $percent; ?>%):</b> <span style="color:#22d3ee"><?php echo money($deposit); ?> VNĐ</span></p>
        <p><b>Nội dung CK:</b> <span id="memo"><?php echo htmlspecialchars($memo); ?></span>
           <button class="copy-btn" data-copy="#memo">Sao chép</button></p>
        <hr style="border-color:#374151; margin:14px 0">
        <p><b>Chủ TK:</b> <span id="accName"><?php echo htmlspecialchars($accountNm); ?></span>
           <button class="copy-btn" data-copy="#accName">Sao chép</button></p>
        <p>— <b>STK:</b> <span id="accNo"><?php echo htmlspecialchars($accountNo); ?></span>
           <button class="copy-btn" data-copy="#accNo">Sao chép</button></p>
        
      </div>
    </div>

    <div class="card-dark">
      <div class="qr-box">
        <img class="qr" id="qrImg" src="<?php echo htmlspecialchars($qrUrl); ?>" alt="QR chuyển khoản">
      </div>
      <div id="qrError" class="notice" style="display:none">
        ⚠️ Không tải được ảnh QR. Vui lòng kiểm tra lại <b>BIN</b> và <b>STK</b> trong phần Cài đặt,
        hoặc nhập tên ngân hàng thành BIN số (ví dụ: VCB = 970436).
      </div>
    </div>
  </div>

  <a class="btn-floating" href="my_bookings.php">Về đơn của tôi</a>
</main>

<script>
  // Copy to clipboard
  document.querySelectorAll('.copy-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const sel = btn.getAttribute('data-copy');
      const el  = document.querySelector(sel);
      if (!el) return;
      const text = el.textContent.trim();
      navigator.clipboard.writeText(text).then(()=>{
        btn.textContent = 'Đã chép';
        setTimeout(()=>btn.textContent='Sao chép', 1200);
      });
    });
  });

  // Báo lỗi nếu ảnh QR load fail
  const qrImg = document.getElementById('qrImg');
  const qrErr = document.getElementById('qrError');
  qrImg.addEventListener('error', ()=>{ qrErr.style.display='block'; });
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
