<?php
// public/admin/chat_console.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// yêu cầu quyền admin/staff
if (!isset($_SESSION['user']) || !in_array(($_SESSION['user']['role'] ?? ''), ['admin','staff'])) {
  header('Location: ../login.php'); exit;
}

$chatId = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;
$chats = $pdo->query("SELECT c.id, c.user_id, c.status, c.created_at, u.username
                      FROM chats c JOIN users u ON u.id=c.user_id
                      WHERE c.status='open' ORDER BY c.updated_at DESC, c.id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__.'/header.php'; ?>
<div class="container mt-4">
  <h3>Hộp thoại CSKH</h3>
  <div class="row">
    <div class="col-md-4">
      <ul class="list-group">
        <?php foreach($chats as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <a href="?chat_id=<?=$c['id']?>">Chat #<?=$c['id']?> • <?=$c['username']?></a>
            <span class="badge bg-secondary"><?=$c['status']?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="col-md-8">
      <?php if ($chatId): ?>
        <div id="panel" style="border:1px solid #ddd;border-radius:8px; padding:10px;">
          <div id="msgs" style="height:380px; overflow:auto; background:#f8fafc; padding:10px; border-radius:6px;"></div>
          <form id="frm" class="mt-2 d-flex gap-2">
            <input type="text" id="inp" class="form-control" placeholder="Nhập trả lời...">
            <button class="btn btn-primary">Gửi</button>
          </form>
        </div>
        <script>
          let lastId=0;
          async function pull(){
            const r = await fetch(`/api/chat/pull.php?chat_id=<?=$chatId?>&since_id=${lastId}`,{credentials:'same-origin'});
            const j = await r.json();
            if (j.ok){
              const box = document.getElementById('msgs');
              (j.messages||[]).forEach(m=>{
                const d = document.createElement('div');
                d.style.margin='6px 0';
                d.innerHTML = `<span class="badge ${m.sender_type==='staff'?'bg-primary':'bg-secondary'}">${m.sender_type}</span> `+
                              `<span>${m.message.replace(/[&<>"]/g,s=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]))}</span>`+
                              ` <small class="text-muted">${m.ts||''}</small>`;
                box.appendChild(d);
              });
              if (j.messages?.length) box.scrollTop = box.scrollHeight;
              lastId = j.last_id || lastId;
            }
          }
          setInterval(pull, 2000); pull();
          document.getElementById('frm').addEventListener('submit', async (e)=>{
            e.preventDefault();
            const t = document.getElementById('inp').value.trim();
            if(!t) return;
            document.getElementById('inp').value='';
            await fetch('/api/chat/send.php',{method:'POST',credentials:'same-origin',
              headers:{'Content-Type':'application/x-www-form-urlencoded'},
              body:new URLSearchParams({chat_id:<?=$chatId?>, message:t})
            });
            pull();
          });
        </script>
      <?php else: ?>
        <div class="alert alert-info">Chọn một phiên chat bên trái để trả lời.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__.'/footer.php'; ?>
