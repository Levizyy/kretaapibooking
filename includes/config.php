<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kereta_api_db');

// Session handling - MUST be called BEFORE any output or connection attempt
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Helper functions
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function password_hash_custom($password) {
    return md5($password);
}

function format_currency($amount) {
    return "Rp " . number_format($amount, 0, ',', '.');
}

function format_date($date) {
    if (!$date || $date === '0000-00-00') return '-';
    return date('d-m-Y', strtotime($date));
}

function format_datetime($datetime) {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '-';
    return date('d-m-Y H:i:s', strtotime($datetime));
}

function get_user_role($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $query = "SELECT role FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['role'];
    }
    return null;
}

function get_base_url() {
    // Auto-detect base URL based on script location
    $script = $_SERVER['SCRIPT_NAME'];
    // Remove /admin/xxx.php or /user/xxx.php to get base
    $base = preg_replace('#/(admin|user)/[^/]+$#', '', $script);
    $base = preg_replace('#/[^/]+\.php$#', '', $base);
    return $base;
}

function check_admin_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $base = get_base_url();
        header("Location: $base/login.php");
        exit();
    }
}

function check_user_login() {
    if (!isset($_SESSION['user_id'])) {
        $base = get_base_url();
        header("Location: $base/login.php");
        exit();
    }
}

// API Response Helper
function api_response($success, $message = '', $data = []) {
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
}
?>
