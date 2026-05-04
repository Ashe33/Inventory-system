<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id       = (int) $_POST['id'];
    $name     = trim($_POST['name']);
    $quantity = (int) $_POST['quantity'];
    $category = trim($_POST['category']);

    $stmt = $conn->prepare("UPDATE products SET name=?, quantity=?, category=? WHERE id=?");
    $stmt->bind_param("sisi", $name, $quantity, $category, $id);
    $stmt->execute();
}

header("Location: dashboard.php?page=products");
exit();
?>