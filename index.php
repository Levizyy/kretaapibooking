<?php
require 'includes/config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check user role and redirect accordingly
$role = get_user_role($_SESSION['user_id']);
if ($role === 'admin') {
    header("Location: admin/");
} else {
    header("Location: user/");
}
exit();
?>
