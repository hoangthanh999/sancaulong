<?php
// public/admin/stats.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// ===== helpers =====
function hasColumn(PDO $pdo,$t,$c){
  $st=$pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1");
  $st->execute([$t,$c]);return (bool)$st->fetchColumn();
}
function hasTable(PDO $pdo,$t){
  $st=$pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1");
  $st->execute([$t]);return (bool)$st->fetchColumn();
}
function fmt_money($v){return number_format((int)$v,0,',','.').'đ';}

$has_total_price    = hasColumn($pdo,'bookings','total_price');
$has_deposit        = hasColumn($pdo,'bookings','deposit');
$has_deposit_status = hasColumn($pdo,'bookings','deposit_status');
$has_users_created  = hasColumn($pdo,'users','created_at');
$has_timeslot_id    = hasColumn($pdo,'bookings','timeslot_id');
$has_timeslot_text  = hasColumn($pdo,'bookings','timeslot');
$has_timeslots_tbl  = hasTable($pdo,'timeslots');

// ===== filters =====
$today = date('Y-m-d');
$start = isset($_GET['start']) && $_GET['start']!=='' ? $_GET['start'] : date('Y-m-d', strtotime('-6 days', strtotime($today)));
$end   = isset($_GET['end'])   && $_GET['end']  !=='' ? $_GET['end']   : $today;
if ($start>$end){$t=$start;$start=$end;$end=$t;}

$court_id   = isset($_GET['court_id']) ? (int)$_GET['court_id'] : 0;
$slot_id    = isset($_GET['timeslot_id']) ? (int)$_GET['timeslot_id'] : 0;
$slot_label = isset($_GET['timeslot_label']) ? trim($_GET['timeslot_label']) : '';

// lists for dropdowns
$courts = $pdo->query("SELECT id,name FROM courts WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$timeslots = $has_timeslots_tbl
  ? $pdo->query("SELECT id,label FROM timeslots WHERE status='active' ORDER BY start_time")->fetchAll(PDO::FETCH_ASSOC)
  : [];

// ===== build base WHERE/JOIN once =====
$where   = " WHERE b.booking_date BETWEEN ? AND ? AND b.status IN ('approved','pending')";
$params  = [$start,$end];
$join    = "";
if (!$has_total_price) $join .= " JOIN courts c ON c.id=b.court_id";

if ($court_id>0){ $where.=" AND b.court_id=?"; $params[]=$court_id; }

if ($has_timeslot_id && $slot_id>0){
  $where.=" AND b.timeslot_id=?"; $params[]=$slot_id;
} elseif ($has_timeslot_text && $slot_label!==''){
  $where.=" AND b.timeslot=?";    $params[]=$slot_label;
}

// ===== revenue + count by day =====
$selectRevenue = $has_total_price
  ? "COALESCE(SUM(b.total_price),0) AS revenue"
  : "COALESCE(SUM(c.price_per_hour),0) AS revenue";

$sqlRev = "SELECT b.booking_date AS d, $selectRevenue, COUNT(*) AS cnt
           FROM bookings b $join $where
           GROUP BY b.booking_date ORDER BY b.booking_date";
$st = $pdo->prepare($sqlRev); $st->execute($params);
$rowsRev = $st->fetchAll(PDO::FETCH_ASSOC);

// ===== deposit paid by day =====
$rowsDep=[];
if ($has_deposit && $has_deposit_status){
  $whereDep   = str_replace("AND b.status IN ('approved','pending')","AND b.deposit_status='paid'",$where);
  $sqlDep = "SELECT b.booking_date AS d, COALESCE(SUM(b.deposit),0) AS deposit_paid
             FROM bookings b $whereDep
             GROUP BY b.booking_date ORDER BY b.booking_date";
  $st=$pdo->prepare($sqlDep); $st->execute($params);
  $rowsDep=$st->fetchAll(PDO::FETCH_ASSOC);
}

// ===== new users by day =====
$rowsNew=[];
if ($has_users_created){
  $st=$pdo->prepare("SELECT DATE(created_at) AS d, COUNT(*) AS new_users
                     FROM users WHERE DATE(created_at) BETWEEN ? AND ?
                     GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
  $st->execute([$start,$end]); $rowsNew=$st->fetchAll(PDO::FETCH_ASSOC);
}

// ===== merge per day =====
$days=[];
for($d=strtotime($start);$d<=strtotime($end);$d=strtotime('+1 day',$d)){
  $k=date('Y-m-d',$d); $days[$k]=['revenue'=>0,'cnt'=>0,'deposit_paid'=>0,'new_users'=>0];
}
foreach($rowsRev as $r){ $k=$r['d']; if(isset($days[$k])){$days[$k]['revenue']=(int)$r['revenue']; $days[$k]['cnt']=(int)$r['cnt']; } }
foreach($rowsDep as $r){ $k=$r['d']; if(isset($days[$k])){$days[$k]['deposit_paid']=(int)$r['deposit_paid']; } }
foreach($rowsNew as $r){ $k=$r['d']; if(isset($days[$k])){$days[$k]['new_users']=(int)$r['new_users']; } }

$totalRevenue=$totalDeposit=$totalBookings=$totalUsers=0;
$labels=$dataRevenue=$dataDeposit=$dataBookings=[];
foreach($days as $k=>$v){
  $totalRevenue+=$v['revenue']; $totalDeposit+=$v['deposit_paid']; $totalBookings+=$v['cnt']; $totalUsers+=$v['new_users'];
  $labels[]=date('d/m',strtotime($k)); $dataRevenue[]=$v['revenue']; $dataDeposit[]=$v['deposit_paid']; $dataBookings[]=$v['cnt'];
}

// ===== export CSV (Excel) =====
if (isset($_GET['export']) && $_GET['export']==='csv') {
  $filename = "stats_{$start}_to_{$end}";
  if($court_id>0){ $filename .= "_court{$court_id}"; }
  if($slot_id>0){ $filename .= "_slot{$slot_id}"; }
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
  // UTF-8 BOM để Excel hiển thị tiếng Việt đúng
  echo "\xEF\xBB\xBF";
  echo "Ngày,Doanh thu,Cọc đã nhận,Số đơn,Khách mới\n";
  foreach($days as $k=>$v){
    echo date('d/m/Y',strtotime($k)).",".
         $v['revenue'].",".
         $v['deposit_paid'].",".
         $v['cnt'].",".
         $v['new_users']."\n";
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thống kê - BS Badminton</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    *{box-sizing:border-box} body{font-family:Segoe UI,Tahoma,Verdana,sans-serif;background:#f7fafc;color:#2d3748;margin:0}
    .wrap{max-width:1200px;margin:24px auto;padding:0 16px}
    h2{margin:0 0 16px}
    .filters{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}
    .filters input,.filters select,.filters button,.filters a{padding:8px 10px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;text-decoration:none;color:#2d3748}
    .filters .spacer{flex:1}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;margin-bottom:16px}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px}
    .val{font-size:1.6rem;font-weight:700;margin-top:8px}
    .muted{color:#718096;font-size:.9rem}
    canvas{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px}
    table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden}
    th,td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:left}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Thống kê doanh thu</h2>

    <form class="filters" method="get">
      <span class="muted">Từ</span>
      <input type="date" name="start" value="<?php echo htmlspecialchars($start); ?>">
      <span class="muted">đến</span>
      <input type="date" name="end" value="<?php echo htmlspecialchars($end); ?>">

      <select name="court_id">
        <option value="0">-- Tất cả sân --</option>
        <?php foreach($courts as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo $court_id==(int)$c['id']?'selected':''; ?>>
            <?php echo htmlspecialchars($c['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <?php if ($has_timeslots_tbl && $has_timeslot_id): ?>
        <select name="timeslot_id">
          <option value="0">-- Tất cả khung giờ --</option>
          <?php foreach($timeslots as $t): ?>
            <option value="<?php echo (int)$t['id']; ?>" <?php echo $slot_id==(int)$t['id']?'selected':''; ?>>
              <?php echo htmlspecialchars($t['label']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($has_timeslots_tbl && $has_timeslot_text): ?>
        <select name="timeslot_label">
          <option value="">-- Tất cả khung giờ --</option>
          <?php foreach($timeslots as $t): ?>
            <option value="<?php echo htmlspecialchars($t['label']); ?>" <?php echo $slot_label===$t['label']?'selected':''; ?>>
              <?php echo htmlspecialchars($t['label']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($has_timeslot_text): ?>
        <input type="text" name="timeslot_label" placeholder="Khung giờ (vd: 07:00-08:00)" value="<?php echo htmlspecialchars($slot_label); ?>">
      <?php endif; ?>

      <button type="submit"><i class="fas fa-filter"></i> Lọc</button>
      <a class="spacer"></a>
      <a href="?start=<?php echo urlencode($start); ?>&end=<?php echo urlencode($end); ?>&court_id=<?php echo (int)$court_id; ?>&<?php
          echo $has_timeslot_id ? 'timeslot_id='.(int)$slot_id : 'timeslot_label='.urlencode($slot_label);
        ?>&export=csv"><i class="fas fa-file-excel"></i> Xuất Excel</a>
      <a href="dashboard.php">← Quay lại</a>
    </form>

    <div class="grid">
      <div class="card"><div class="muted">Tổng doanh thu</div><div class="val"><?php echo fmt_money($totalRevenue); ?></div></div>
      <div class="card"><div class="muted">Cọc đã nhận</div><div class="val"><?php echo fmt_money($totalDeposit); ?></div></div>
      <div class="card"><div class="muted">Số đơn</div><div class="val"><?php echo number_format($totalBookings,0,',','.'); ?></div></div>
      <div class="card"><div class="muted">Khách mới</div><div class="val"><?php echo number_format($totalUsers,0,',','.'); ?></div></div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <canvas id="revChart" height="110"></canvas>
    </div>

    <table>
      <thead>
        <tr>
          <th>Ngày</th>
          <th>Doanh thu</th>
          <th>Cọc đã nhận</th>
          <th>Số đơn</th>
          <th>Khách mới</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($days as $k=>$v): ?>
          <tr>
            <td><?php echo date('d/m/Y', strtotime($k)); ?></td>
            <td><?php echo fmt_money($v['revenue']); ?></td>
            <td><?php echo fmt_money($v['deposit_paid']); ?></td>
            <td><?php echo number_format($v['cnt'],0,',','.'); ?></td>
            <td><?php echo number_format($v['new_users'],0,',','.'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    const labels   = <?php echo json_encode($labels); ?>;
    const revenue  = <?php echo json_encode($dataRevenue); ?>;
    const deposit  = <?php echo json_encode($dataDeposit); ?>;
    const bookings = <?php echo json_encode($dataBookings); ?>;
    new Chart(document.getElementById('revChart'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          {label:'Doanh thu', data:revenue, tension:.3},
          {label:'Cọc đã nhận', data:deposit, tension:.3},
          {label:'Số đơn', data:bookings, yAxisID:'y2', tension:.3}
        ]
      },
      options: {
        scales: {
          y: {beginAtZero:true},
          y2:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}}
        }
      }
    });
  </script>
</body>
</html>
