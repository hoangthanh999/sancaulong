<?php
// public/schedule.php
session_start();
require_once __DIR__ . '/../config/db.php';

$date = $_GET['date'] ?? date('Y-m-d');

$courts = $pdo->query("SELECT id,name FROM courts WHERE active=1 ORDER BY name")->fetchAll();
$slots  = $pdo->query("SELECT id,label FROM timeslots WHERE active=1 ORDER BY start_time")->fetchAll();

$busyMap = []; // [court_id][timeslot_id] = true
$st = $pdo->prepare("SELECT court_id,timeslot_id FROM bookings WHERE booking_date=? AND status IN ('pending','approved')");
$st->execute([$date]);
foreach ($st->fetchAll() as $b) {
  $busyMap[$b['court_id']][$b['timeslot_id']] = true;
}

@include __DIR__ . '/partials/header.php';
?>
<main class="container mt-4">
  <h2 class="mb-3">Lịch trống ngày <?php echo date('d/m/Y', strtotime($date)); ?></h2>
  <form class="mb-3">
    <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
    <button class="btn btn-sm btn-primary">Xem</button>
  </form>

  <div class="table-responsive">
    <table class="table table-dark table-bordered align-middle">
      <thead>
        <tr>
          <th>Sân / Khung giờ</th>
          <?php foreach ($slots as $s): ?>
            <th class="text-center"><?php echo htmlspecialchars($s['label']); ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courts as $c): ?>
          <tr>
            <th><?php echo htmlspecialchars($c['name']); ?></th>
            <?php foreach ($slots as $s): 
              $busy = !empty($busyMap[$c['id']][$s['id']]); ?>
              <td class="text-center <?php echo $busy ? 'table-danger' : 'table-success'; ?>">
                <?php echo $busy ? 'Bận' : 'Trống'; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php @include __DIR__ . '/partials/footer.php'; ?>
