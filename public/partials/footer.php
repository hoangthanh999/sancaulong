<?php
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($BASE === '') $BASE = '.';
$BASE_ESC = htmlspecialchars($BASE, ENT_QUOTES);
$VER = time();
?>

<!-- Bong bóng Chat: launcher + panel -->
<link rel="stylesheet" href="<?= $BASE_ESC ?>/assets/css/chat.css?v=<?= $VER ?>">
<div id="chat-launcher" class="chat-launcher"></div>
<div id="support-bubble" class="chat-bubble"></div>
<script>window.CHAT_BASE = "<?= $BASE_ESC ?>";</script>
<script src="<?= $BASE_ESC ?>/assets/js/chat.js?v=<?= $VER ?>"></script>

</body>
</html>
