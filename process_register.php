<?php
session_start();
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get inputs
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phonenumber']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    // Validate phone format only (NOT unique check)
    if (!preg_match('/^\+639\d{9}$/', $phone)) {
        $_SESSION['msg'] = "Phone must be in format +639XXXXXXXXX only!";
        $_SESSION['type'] = "error";
        header("Location: register.php");
        exit();
    }

    // Password match check
    if ($password !== $confirm) {
        $_SESSION['msg'] = "Passwords do not match!";
        $_SESSION['type'] = "error";
        header("Location: register.php");
        exit();
    }

    // Password length check
    if (strlen($password) < 8 || strlen($password) > 16) {
        $_SESSION['msg'] = "Password must be 8 to 16 characters!";
        $_SESSION['type'] = "error";
        header("Location: register.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (fullname, phone, username, email, password, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param("sssss", $fullname, $phone, $username, $email, $hashed_password);

    if ($stmt->execute()) {

        $_SESSION['msg'] = "Registration successful! Wait for admin approval before login.";
        $_SESSION['type'] = "success";

        header("Location: login.php");
        exit();

    } else {

        $_SESSION['msg'] = "Something went wrong. Try again!";
        $_SESSION['type'] = "error";

        header("Location: register.php");
        exit();
    }

} else {
    header("Location: register.php");
    exit();
}
?>