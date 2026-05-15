<?php
session_start();
include 'config/database.php';

$mailerAvailable = false;
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mailerAvailable = true;
    }
}

function has_internet() {
    $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 3);
    if ($connected) {
        fclose($connected);
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Username and password are required.";
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login.php");
    exit();
}

if ($user['status'] != 'approved') {
    $_SESSION['login_error'] = "Account not approved yet. Please wait for admin approval.";
    header("Location: login.php");
    exit();
}

if (!has_internet()) {
    $_SESSION['login_error'] = "No internet connection detected. OTP required.";
    header("Location: login.php");
    exit();
}

if (!$mailerAvailable) {
    $_SESSION['login_error'] = "Email system unavailable.";
    header("Location: login.php");
    exit();
}

if (!$user['email'] || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = "Invalid email on record.";
    header("Location: login.php");
    exit();
}

/* =========================
   OTP GENERATION
========================= */
$otp        = (string) rand(100000, 999999);
$otp_expiry = time() + 300; // 5 minutes

/* =========================
   SEND EMAIL
========================= */
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'hoyoajohnashley27@gmail.com';
    $mail->Password   = 'fvbu rbvk ckfg osgw';
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('hoyoajohnashley27@gmail.com', 'StockFlow IMS');
    $mail->addAddress($user['email']);
    $mail->isHTML(false);
    $mail->Subject = "StockFlow IMS — Your OTP Code";
    $mail->Body    = "Hello {$user['username']},\n\nYour OTP is: {$otp}\n\nThis code expires in 5 minutes.\n\nIf you did not request this, ignore this email.";
    $mail->send();

} catch (Exception $e) {
    $_SESSION['login_error'] = "Email failed: " . $mail->ErrorInfo;
    header("Location: login.php");
    exit();
}

/* =========================
   SAVE OTP TO DB (after email confirmed sent)
========================= */
$stmt2 = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?");
$stmt2->bind_param("sii", $otp, $otp_expiry, $user['id']);
$stmt2->execute();

/* =========================
   SAVE TEMP SESSION
   — use 'temp_user' so otp_verification_process.php can read it
========================= */
$_SESSION['temp_user'] = $user['username'];
$_SESSION['temp_role'] = $user['role'];

header("Location: otp_verification.php");
exit();
?>