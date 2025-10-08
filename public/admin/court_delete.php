<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM courts WHERE id=?");
$stmt->execute([$id]);

header("Location: courts.php");
exit;
