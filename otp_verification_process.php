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

/* Validate */
if (!ctype_digit($otp) || strlen($otp) != 6) {
    $_SESSION['otp_error'] = "OTP must be exactly 6 digits.";
    header("Location: otp_verification.php");
    exit();
}

/* Session temp */
$temp_user = $_SESSION['temp_user'];

/* Get user by USERNAME */
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $temp_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['otp_error'] = "User not found.";
    header("Location: login.php");
    exit();
}

/* Check OTP */
if ($user['otp'] != $otp) {
    $_SESSION['otp_error'] = "Invalid OTP code.";
    header("Location: otp_verification.php");
    exit();
}

/* Expiration */
if (time() > $user['otp_expiry']) {
    $_SESSION['otp_error'] = "OTP has expired. Please login again.";
    header("Location: login.php");
    exit();
}

/* SUCCESS — set full session */
$_SESSION['user'] = $user;

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