<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
$user = $_SESSION['user'];
if (!empty($user['is_admin']) && (int)$user['is_admin'] === 1) { header("Location: admin/dashboard.php"); exit; }

/* Stats */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?"); $stmt->execute([$user['id']]);
$totalBookings = (int)($stmt->fetchColumn() ?? 0);
$stmt = $pdo->prepare("SELECT SUM(total_price) FROM bookings WHERE user_id = ?"); $stmt->execute([$user['id']]);
$totalSpent = (int)($stmt->fetchColumn() ?? 0);
$stmt = $pdo->query("SELECT COUNT(*) FROM courts"); $totalCourts = (int)($stmt->fetchColumn() ?? 0);
?>

<?php include 'partials/header.php'; ?>

<main class="main-container">
  <?php include 'partials/welcome.php'; ?>
  <?php include 'partials/stats.php'; ?>
  <?php include 'partials/actions.php'; ?>
  <?php include 'partials/recent.php'; ?>
</main>

<?php
// Bong bóng chat
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($BASE === '') $BASE = '.';
$BASE_ESC = htmlspecialchars($BASE, ENT_QUOTES);
?>
<link rel="stylesheet" href="<?= $BASE_ESC ?>/assets/css/chat.css?v=3">
<div id="support-bubble" class="chat-bubble"></div>
<script>window.CHAT_BASE = "<?= $BASE_ESC ?>";</script>
<script src="<?= $BASE_ESC ?>/assets/js/chat.js?v=3"></script>

<?php include 'partials/footer.php'; ?>
