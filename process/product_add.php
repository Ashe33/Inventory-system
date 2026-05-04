<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $quantity = (int) $_POST['quantity'];
    $category = trim($_POST['category']);

    $stmt = $conn->prepare("INSERT INTO products (name, quantity, category) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $name, $quantity, $category);
    $stmt->execute();
}

header("Location: ../dashboard.php?page=products");
exit();
?>