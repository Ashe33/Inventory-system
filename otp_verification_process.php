<?php
session_start();
include 'config/database.php';

/* Prevent direct access */
if (!isset($_SESSION['temp_user'])) {
    $_SESSION['login_error'] = "Session expired. Please login again.";
    header("Location: login.php");
    exit();
}

/* POST only */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: otp_verification.php");
    exit();
}

$otp = trim($_POST['otp'] ?? '');

/* Validate OTP format */
if (!ctype_digit($otp) || strlen($otp) != 6) {
    $_SESSION['otp_error'] = "OTP must be exactly 6 digits.";
    header("Location: otp_verification.php");
    exit();
}

/* Get username from temp session */
$temp_user = $_SESSION['temp_user'];

/* Get user from DB */
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $temp_user);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if (!$user) {
    $_SESSION['otp_error'] = "User not found.";
    header("Location: login.php");
    exit();
}

/* Check OTP match */
if ((string) $user['otp'] !== (string) $otp) {
    $_SESSION['otp_error'] = "Invalid OTP code.";
    header("Location: otp_verification.php");
    exit();
}

/* Check expiry */
$otp_expiry = (int) $user['otp_expiry'];
$now        = time();

if ($otp_expiry === 0 || $now > $otp_expiry) {
    $_SESSION['otp_error'] = "OTP has expired. Please login again.";

    // Clear expired OTP
    $stmt_clear = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE id = ?");
    $stmt_clear->bind_param("i", $user['id']);
    $stmt_clear->execute();

    header("Location: login.php");
    exit();
}

/* SUCCESS — set full session */
$_SESSION['user'] = [
    'id'       => $user['id'],
    'username' => $user['username'],
    'email'    => $user['email'],
    'role'     => strtolower(trim($user['role'])),
    'status'   => $user['status']
];

/* Remove temp session */
unset($_SESSION['temp_user']);
unset($_SESSION['temp_role']);

/* Clear OTP from DB */
$stmt2 = $conn->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE id = ?");
$stmt2->bind_param("i", $user['id']);
$stmt2->execute();

/* Redirect to dashboard */
header("Location: dashboard.php");
exit();
?>