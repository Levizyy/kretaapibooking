<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();

// Dynamic base URL detection
$script = $_SERVER['SCRIPT_NAME'];
$base = dirname($script); // e.g., /kereta_api
header("Location: $base/login.php");
exit();
?>
