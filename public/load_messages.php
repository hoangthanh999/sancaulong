<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query("SELECT m.message, m.created_at, u.name, u.avatar, u.id as user_id
                     FROM messages m 
                     JOIN users u ON m.user_id = u.id 
                     ORDER BY m.created_at ASC");
$messages = $stmt->fetchAll();

$colors = ["primary", "success", "danger", "warning", "info", "secondary", "dark"];

foreach ($messages as $msg) {
    $color = $colors[$msg['user_id'] % count($colors)];
    echo "<div class='d-flex align-items-start mb-3'>";
    
    // avatar
    if (!empty($msg['avatar'])) {
        echo "<img src='" . htmlspecialchars($msg['avatar']) . "' 
                 class='rounded-circle me-2' width='40' height='40'>";
    } else {
        echo "<div class='rounded-circle bg-$color text-white d-flex align-items-center justify-content-center me-2' 
                  style='width:40px; height:40px; font-size:18px;'>
                  <i class='fa-solid fa-user'></i>
              </div>";
    }
    
    // nội dung
    echo "<div>";
    echo "<div><strong class='text-$color'>" . htmlspecialchars($msg['name']) . "</strong></div>";
    echo "<div>" . nl2br(htmlspecialchars($msg['message'])) . "</div>";
    echo "<small class='text-muted'>" . $msg['created_at'] . "</small>";
    echo "</div>";
    
    echo "</div>";
}
