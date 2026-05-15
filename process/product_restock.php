<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php?page=products");
    exit();
}

$id           = (int) ($_POST['id'] ?? 0);
$add_quantity = (int) ($_POST['add_quantity'] ?? 0);

if (!$id || $add_quantity <= 0) {
    header("Location: ../dashboard.php?page=products&error=invalid");
    exit();
}

$conn->query("UPDATE products SET quantity = quantity + $add_quantity WHERE id=$id");

header("Location: ../dashboard.php?page=products&success=stocked");
exit();
?>
