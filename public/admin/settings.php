<?php
// public/admin/settings.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// (tùy chọn) chặn non-admin
// if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
//   header('Location: ../login.php'); exit;
// }

function ensureSettingsTable(PDO $pdo){
  $sql = "CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(100) PRIMARY KEY,
            `value` TEXT NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
  $pdo->exec($sql);
}
function set_setting(PDO $pdo,$key,$value){
  $st=$pdo->prepare("REPLACE INTO settings(`key`,`value`) VALUES(?,?)");
  $st->execute([$key,$value]);
}
function get_setting(PDO $pdo,$key,$default=''){
  $st=$pdo->prepare("SELECT `value` FROM settings WHERE `key`=?");
  $st->execute([$key]);
  $v=$st->fetchColumn();
  return ($v===false)?$default:$v;
}
ensureSettingsTable($pdo);

$flash = ['ok'=>'','err'=>''];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $site_name  = trim($_POST['site_name'] ?? 'BS Badminton');
    $deposit_pc = (int)($_POST['deposit_percent'] ?? 15);
    if ($deposit_pc < 0) $deposit_pc = 0;
    if ($deposit_pc > 100) $deposit_pc = 100;

    $bank_code  = trim($_POST['bank_code'] ?? 'vietcombank'); // hoặc BIN 970436
    $account_no = trim($_POST['account_no'] ?? '');
    $account_nm = trim($_POST['account_name'] ?? '');
    $qr_tmpl    = trim($_POST['qr_template'] ?? 'compact');
    if (!in_array($qr_tmpl, ['compact','compact2','qr_only','print'], true)) $qr_tmpl = 'compact';

    $pdo->beginTransaction();
    set_setting($pdo,'site_name',$site_name);
    set_setting($pdo,'deposit_percent',$deposit_pc);
    set_setting($pdo,'bank_code',$bank_code);
    set_setting($pdo,'account_no',$account_no);
    set_setting($pdo,'account_name',$account_nm);
    set_setting($pdo,'qr_template',$qr_tmpl);
    $pdo->commit();
    $flash['ok'] = 'Đã lưu cài đặt.';
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $flash['err'] = 'Lỗi: '.$e->getMessage();
  }
}

// nạp giá trị hiện tại
$site_name   = get_setting($pdo,'site_name','BS Badminton');
$deposit_pc  = get_setting($pdo,'deposit_percent','15');
$bank_code   = get_setting($pdo,'bank_code','vietcombank'); // ví dụ: vietcombank hoặc 970436
$account_no  = get_setting($pdo,'account_no','');
$account_nm  = get_setting($pdo,'account_name','');
$qr_tmpl     = get_setting($pdo,'qr_template','compact');

// link quay lại
$back = 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cài đặt - BS Badminton</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    *{box-sizing:border-box} body{font-family:Segoe UI,Tahoma,Verdana,sans-serif;background:#f7fafc;color:#2d3748;margin:0}
    .wrap{max-width:900px;margin:24px auto;padding:0 16px}
    h2{margin:0 0 16px}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px}
    .row{display:flex;gap:16px;flex-wrap:wrap}
    .col{flex:1 1 300px}
    label{display:block;margin:8px 0 6px;font-weight:600}
    input,select,button,textarea{width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:#fff}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
    .btn-primary{background:#667eea;color:#fff;border-color:#667eea;cursor:pointer}
    .btn{padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;text-decoration:none;color:#2d3748}
    .alert{padding:10px 12px;border-radius:8px;margin-bottom:12px}
    .alert-ok{background:#e6fffa;border:1px solid #81e6d9;color:#234e52}
    .alert-err{background:#fff5f5;border:1px solid #feb2b2;color:#742a2a}
    small{color:#718096}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Cài đặt hệ thống</h2>

    <?php if ($flash['ok']): ?><div class="alert alert-ok"><?php echo htmlspecialchars($flash['ok']); ?></div><?php endif; ?>
    <?php if ($flash['err']): ?><div class="alert alert-err"><?php echo htmlspecialchars($flash['err']); ?></div><?php endif; ?>

    <form method="post" class="card">
      <div class="row">
        <div class="col">
          <label>Tên hệ thống</label>
          <input name="site_name" value="<?php echo htmlspecialchars($site_name); ?>">
        </div>
        <div class="col">
          <label>% cọc (0-100)</label>
          <input type="number" min="0" max="100" name="deposit_percent" value="<?php echo htmlspecialchars($deposit_pc); ?>">
          <small>Đơn hiện tại vẫn cọc 15% nếu code đặt sân không đọc setting này. Có thể chỉnh theo hướng dẫn bên dưới.</small>
        </div>
      </div>

      <h3 style="margin-top:16px">Thông tin VietQR</h3>
      <div class="row">
        <div class="col">
          <label>Bank ID / BIN</label>
          <input name="bank_code" value="<?php echo htmlspecialchars($bank_code); ?>">
          <small>Ví dụ: <b>vietcombank</b> hoặc BIN <b>970436</b>. (img.vietqr.io không dùng mã VCB/TCB 3 ký tự)</small>
        </div>
        <div class="col">
          <label>Số tài khoản</label>
          <input name="account_no" value="<?php echo htmlspecialchars($account_no); ?>">
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label>Chủ tài khoản</label>
          <input name="account_name" value="<?php echo htmlspecialchars($account_nm); ?>">
        </div>
        <div class="col">
          <label>Template QR</label>
          <select name="qr_template">
            <?php foreach (['compact','compact2','qr_only','print'] as $tpl): ?>
              <option value="<?php echo $tpl; ?>" <?php echo $qr_tmpl===$tpl?'selected':''; ?>><?php echo $tpl; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="actions">
        <a class="btn" href="<?php echo htmlspecialchars($back); ?>">← Quay lại</a>
        <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>

    <div class="card" style="margin-top:16px">
      <h3>Gợi ý tích hợp nhanh</h3>
      <p><b>1) Dùng % cọc trong đặt sân</b> – mở <code>public/booking_process.php</code> và thay đoạn tính cọc:</p>
      <pre style="white-space:pre-wrap;background:#f7fafc;padding:10px;border-radius:8px;border:1px solid #e2e8f0;"><?php
echo htmlspecialchars(
"// ...
// Lấy % cọc từ settings (mặc định 15%)
\$st = \$pdo->prepare(\"SELECT value FROM settings WHERE `key`='deposit_percent'\");
\$st->execute(); \$pc = (int)(\$st->fetchColumn() ?: 15);
\$deposit = (int) round(\$price * max(0,min(100,\$pc)) / 100);
// ..."); ?></pre>

      <p><b>2) Dùng thông tin VietQR động</b> – trong <code>public/payment.php</code> lấy từ bảng <code>settings</code>:</p>
      <pre style="white-space:pre-wrap;background:#f7fafc;padding:10px;border-radius:8px;border:1px solid #e2e8f0;"><?php
echo htmlspecialchars(
"\$bankCode   = get_setting(\$pdo,'bank_code','vietcombank');
\$accountNo  = get_setting(\$pdo,'account_no','');
\$accountName= get_setting(\$pdo,'account_name','');
\$template   = get_setting(\$pdo,'qr_template','compact');"); ?></pre>
    </div>
  </div>
</body>
</html>
