<?php
require_once __DIR__.'/../../config/db.php';
require_once __DIR__.'/../../middleware/admin_only.php';
include __DIR__.'/../../includes/header.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  $label=trim($_POST['label']??'');
  $start=$_POST['start_time']??''; $end=$_POST['end_time']??'';
  $active=isset($_POST['active'])?1:0;
  if($id){
    $st=$pdo->prepare("UPDATE timeslots SET label=?, start_time=?, end_time=?, active=? WHERE id=?");
    $st->execute([$label,$start,$end,$active,$id]);
    flash('success','Đã cập nhật khung giờ.');
  } else {
    $st=$pdo->prepare("INSERT INTO timeslots(label,start_time,end_time,active) VALUES(?,?,?,?)");
    $st->execute([$label,$start,$end,$active]);
    flash('success','Đã thêm khung giờ.');
  }
}

if(($_GET['delete']??'')!==''){
  $id=(int)$_GET['delete'];
  $pdo->prepare("DELETE FROM timeslots WHERE id=?")->execute([$id]);
  flash('success','Đã xoá khung giờ.');
  header('Location: timeslots.php'); exit;
}

$rows=$pdo->query("SELECT * FROM timeslots ORDER BY start_time")->fetchAll();
?>
<h2>Quản lý khung giờ</h2>
<form method="post" class="card" style="max-width:520px;margin-bottom:16px">
  <h3>Thêm / Sửa</h3>
  <input class="input" name="id" placeholder="ID (để trống nếu thêm mới)">
  <input class="input" name="label" placeholder="Nhãn (VD: 06:00-07:00)" required>
  <div style="display:flex; gap:8px">
    <input class="input" type="time" name="start_time" required>
    <input class="input" type="time" name="end_time" required>
  </div>
  <label><input type="checkbox" name="active" checked> Active</label>
  <button class="btn" style="margin-top:8px">Lưu</button>
</form>
<table class="table">
  <thead><tr><th>ID</th><th>Nhãn</th><th>Từ</th><th>Đến</th><th>Active</th><th></th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= htmlspecialchars($r['label']) ?></td>
      <td><?= htmlspecialchars($r['start_time']) ?></td>
      <td><?= htmlspecialchars($r['end_time']) ?></td>
      <td><?= $r['active']?'✔️':'❌' ?></td>
      <td><a class="btn outline" href="?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Xoá?')">Xoá</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__.'/../../includes/footer.php'; ?>
