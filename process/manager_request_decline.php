<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location:login.php");
    exit();
}

$req_id = (int) ($_GET['id'] ?? 0);

if (!$req_id) {
    header("Location:dashboard.php?page=requests&error=invalid");
    exit();
}

$conn->query("UPDATE requests SET status='declined' WHERE id=$req_id");

header("Location: dashboard.php?page=requests&success=declined");
exit();
?>
