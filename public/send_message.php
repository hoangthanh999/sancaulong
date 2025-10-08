<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) exit;

$user_id = $_SESSION['user']['id'];
$message = trim($_POST['message'] ?? '');

if ($message !== "") {
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $message]);
}
