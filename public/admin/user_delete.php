<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Chỉ cho admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $del = $pdo->prepare("DELETE FROM users WHERE id=?");
    $del->execute([$id]);
}

header("Location: manage_users.php");
exit;
