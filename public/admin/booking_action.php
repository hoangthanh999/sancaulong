<?php
session_start();
require_once __DIR__ . '/../config/db.php';


// Chỉ admin
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id && in_array($action, ['approve', 'cancel'])) {
    $status = $action === 'approve' ? 'approved' : 'cancelled';
    $stmt = $pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
    $stmt->execute([$status, $id]);
}

header("Location: manage_bookings.php?success=1");
exit;
