<?php
function flash($type, $message) {
    if (!isset($_SESSION)) session_start();
    $_SESSION['flash'][$type] = $message;
}

function get_flash() {
    if (!isset($_SESSION)) session_start();
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $msg) {
            echo "<div class='alert alert-$type'>$msg</div>";
        }
        unset($_SESSION['flash']);
    }
}
