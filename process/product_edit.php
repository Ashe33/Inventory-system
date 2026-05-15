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

$id       = (int) ($_POST['id'] ?? 0);
$name     = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$quantity = (int) ($_POST['quantity'] ?? 0);

if (!$id || empty($name) || $quantity < 0) {
    header("Location: ../dashboard.php?page=products&error=invalid");
    exit();
}

$stmt = $conn->prepare("UPDATE products SET name=?, quantity=?, category=? WHERE id=?");
$stmt->bind_param("sisi", $name, $quantity, $category, $id);
$stmt->execute();

header("Location: ../dashboard.php?page=products&success=updated");
exit();
?>
