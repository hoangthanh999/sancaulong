<?php
// public/api/chat/send.php
session_start();
require_once __DIR__ . '/../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$role = $_SESSION['user']['role'] ?? 'user'; // 'admin' | 'staff' | 'user'
$userId = $_SESSION['user']['id'] ?? null;

$chatId = (int)($_POST['chat_id'] ?? 0);
$msg    = trim($_POST['message'] ?? '');

if ($chatId<=0 || $msg==='') {
  http_response_code(400); echo json_encode(['ok'=>false,'error'=>'BAD_REQ']); exit;
}

# kiểm quyền cơ bản
if ($role === 'user') {
  // user chỉ được gửi vào chat của chính mình
  $st = $pdo->prepare("SELECT user_id FROM chats WHERE id=?");
  $st->execute([$chatId]);
  $uid = (int)$st->fetchColumn();
  if ($uid !== (int)$userId) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
  $senderType='user'; $senderId=$userId;
} else {
  // staff/admin được gửi vào bất kỳ chat open
  $senderType='staff'; $senderId=$userId;
  $pdo->prepare("UPDATE chats SET assigned_to=? WHERE id=? AND assigned_to IS NULL")->execute([$userId,$chatId]);
}

$st = $pdo->prepare("INSERT INTO chat_messages(chat_id,sender_type,sender_id,message) VALUES(?,?,?,?)");
$st->execute([$chatId,$senderType,$senderId,$msg]);

echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
