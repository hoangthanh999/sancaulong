<?php
// public/api/chat/open.php
session_start();
require_once __DIR__ . '/../../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']['id'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'AUTH']);
  exit;
}
$userId = (int)$_SESSION['user']['id'];

// kiếm phiên open
$st = $pdo->prepare("SELECT id FROM chats WHERE user_id=? AND status='open' ORDER BY id DESC LIMIT 1");
$st->execute([$userId]);
$chatId = (int)$st->fetchColumn();

if (!$chatId) {
  $pdo->prepare("INSERT INTO chats(user_id,status) VALUES(?, 'open')")->execute([$userId]);
  $chatId = (int)$pdo->lastInsertId();
}

echo json_encode(['ok'=>true,'chat_id'=>$chatId]);
