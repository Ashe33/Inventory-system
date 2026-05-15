<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);

if (!$id) {
    header("Location: ../dashboard.php?page=products&error=invalid");
    exit();
}

$conn->query("DELETE FROM products WHERE id=$id");

header("Location: ../dashboard.php?page=products&success=deleted");
exit();
?>
