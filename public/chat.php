<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat cộng đồng - BS Badminton</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/all.min.css">
  <style>
    body {
      background: #f4f6f9;
    }
    .chat-box {
      height: 500px;
      overflow-y: auto;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      background: #fff;
    }
    .message {
      display: flex;
      align-items: flex-start;
      margin-bottom: 15px;
    }
    .message img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
      object-fit: cover;
    }
    .message-content {
      background: #f1f3f5;
      padding: 10px 15px;
      border-radius: 12px;
      max-width: 70%;
    }
    .message .user {
      font-weight: bold;
      color: #007bff;
    }
    .message .time {
      font-size: 12px;
      color: #888;
      margin-left: 5px;
    }
  </style>
</head>
<body>
<div class="container py-4">
  <h3><i class="fa-solid fa-comments"></i> Phòng chat cộng đồng</h3>

  <div id="chatBox" class="chat-box mb-3"></div>

  <form id="chatForm" class="d-flex">
    <input type="text" id="message" class="form-control me-2" placeholder="Nhập tin nhắn..." required>
    <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Gửi</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Hàm load tin nhắn
function loadMessages() {
  fetch("fetch_messages.php")
    .then(res => res.text())
    .then(data => {
      document.getElementById("chatBox").innerHTML = data;
      let chatBox = document.getElementById("chatBox");
      chatBox.scrollTop = chatBox.scrollHeight;
    });
}

// Gửi tin nhắn
document.getElementById("chatForm").addEventListener("submit", function(e) {
  e.preventDefault();
  let msg = document.getElementById("message").value.trim();
  if (msg === "") return;

  fetch("send_message.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "message=" + encodeURIComponent(msg)
  }).then(() => {
    document.getElementById("message").value = "";
    loadMessages();
  });
});

// Load mỗi 2 giây
setInterval(loadMessages, 2000);
loadMessages();
</script>
</body>
</html>
