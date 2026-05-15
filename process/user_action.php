<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$uid    = (int) ($_GET['id'] ?? 0);

if (!$uid || !$action) {
    header("Location: ../dashboard.php?page=users&error=invalid");
    exit();
}

switch ($action) {
    case 'approve':
        $conn->query("UPDATE users SET status='approved' WHERE id=$uid");
        header("Location: ../dashboard.php?page=users&success=approved");
        break;

    case 'decline':
        $conn->query("UPDATE users SET status='declined' WHERE id=$uid");
        header("Location: ../dashboard.php?page=users&success=declined");
        break;

    case 'delete':
        $conn->query("DELETE FROM users WHERE id=$uid");
        header("Location: ../dashboard.php?page=users&success=deleted");
        break;

    default:
        header("Location: ../dashboard.php?page=users&error=invalid");
        break;
}
exit();
?>
