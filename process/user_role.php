<?php
session_start();
include '../config/database.php';
include '../include/logger.php';

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
   ROLE VALIDATION
========================= */
$allowed_roles = ['staff', 'manager', 'admin'];

$role = strtolower(trim($role));

if (!in_array($role, $allowed_roles)) {
    $role = 'staff';
}

if (!$uid) {
    header("Location: ../dashboard.php?page=users&error=invalid");
    exit();
}

/* =========================
   GET OLD ROLE (FOR LOG)
========================= */
$stmtOld = $conn->prepare("SELECT role, username FROM users WHERE id = ?");
$stmtOld->bind_param("i", $uid);
$stmtOld->execute();
$oldData = $stmtOld->get_result()->fetch_assoc();

$oldRole = $oldData['role'] ?? 'unknown';
$username = $oldData['username'] ?? 'user';

/* =========================
   UPDATE ROLE
========================= */
$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $role, $uid);
$stmt->execute();

/* =========================
   AUDIT LOG (THIS WAS MISSING)
========================= */
logAction(
    $conn,
    $_SESSION['user']['id'],
    "UPDATE_ROLE",
    "Changed {$username} role from {$oldRole} to {$role}"
);

header("Location: ../dashboard.php?page=users&success=role_updated");
exit();
?>