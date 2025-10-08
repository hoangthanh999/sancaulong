<?php
require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query("
  SELECT m.*, u.full_name
  FROM messages m
  JOIN users u ON m.user_id = u.id
  ORDER BY m.created_at DESC
  LIMIT 50
");

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as $msg) {
    echo "<div class='chat-message'>";
    echo "<strong>" . htmlspecialchars($msg['full_name']) . ":</strong> ";
    echo htmlspecialchars($msg['message']);
    echo "<br><small>" . $msg['created_at'] . "</small>";
    echo "</div>";
}
