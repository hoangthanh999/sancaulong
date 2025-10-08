<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Chỉ cho admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT status FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
    $upd = $pdo->prepare("UPDATE users SET status=? WHERE id=?");
    $upd->execute([$newStatus, $id]);
}

header("Location: manage_users.php");
exit;
