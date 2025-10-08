<?php
// public/api/chat/pull.php
session_start();
require_once __DIR__ . '/../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$role   = $_SESSION['user']['role'] ?? 'user';
$userId = $_SESSION['user']['id'] ?? null;

$chatId = (int)($_GET['chat_id'] ?? 0);
$since  = (int)($_GET['since_id'] ?? 0);

if ($chatId<=0) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }

if ($role === 'user') {
  $st = $pdo->prepare("SELECT user_id FROM chats WHERE id=?");
  $st->execute([$chatId]);
  $uid = (int)$st->fetchColumn();
  if ($uid !== (int)$userId) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
}

$sql = "SELECT id, sender_type, message, DATE_FORMAT(created_at,'%H:%i %d/%m') AS ts 
        FROM chat_messages WHERE chat_id=? ";
$params = [$chatId];
if ($since>0) { $sql.=" AND id>?"; $params[]=$since; }
$sql .= " ORDER BY id ASC LIMIT 200";
$st=$pdo->prepare($sql); $st->execute($params);
$rows=$st->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok'=>true,'messages'=>$rows, 'last_id'=> end($rows)['id'] ?? $since]);
