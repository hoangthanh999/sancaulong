<?php
// public/cancel_booking.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user']['id'];
$id = (int)($_GET['id'] ?? 0);

if (!$id) { header("Location: my_bookings.php"); exit; }

$st = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");
$st->execute([$id,$user_id]);

header("Location: my_bookings.php");
