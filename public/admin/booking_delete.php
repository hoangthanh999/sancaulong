<?php
session_start();
require_once __DIR__ . '/../config/db.php';


// Chỉ admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->execute([$id]);
}

header("Location: manage_bookings.php?success=1");
exit;
