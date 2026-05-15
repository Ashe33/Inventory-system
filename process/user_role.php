<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php?page=users");
    exit();
}

$uid  = (int) ($_POST['id'] ?? 0);
$role = trim($_POST['role'] ?? '');

/* =========================
   ROLE VALIDATION (FIXED)
========================= */
$allowed_roles = ['staff', 'manager', 'admin'];

$role = strtolower(trim($role));

if (!in_array($role, $allowed_roles)) {
    $role = 'staff';
}

/* prevent invalid user id */
if (!$uid) {
    header("Location: ../dashboard.php?page=users&error=invalid");
    exit();
}

/* =========================
   UPDATE ROLE
========================= */
$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $role, $uid);
$stmt->execute();

header("Location: ../dashboard.php?page=users&success=role_updated");
exit();
?>