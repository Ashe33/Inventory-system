<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php?page=my_requests");
    exit();
}

$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity   = (int) ($_POST['quantity'] ?? 0);
$user_id    = $_SESSION['user']['id'];

if (!$product_id || $quantity <= 0) {
    header("Location: ../dashboard.php?page=my_requests&error=invalid");
    exit();
}

$check   = $conn->query("SELECT * FROM products WHERE id=$product_id");
$product = $check->fetch_assoc();

if (!$product) {
    header("Location: ../dashboard.php?page=my_requests&error=notfound");
    exit();
}

if ($quantity > $product['quantity']) {
    header("Location: ../dashboard.php?page=my_requests&error=stock&max={$product['quantity']}");
    exit();
}

$stmt = $conn->prepare("INSERT INTO requests (user_id, product_id, quantity, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iii", $user_id, $product_id, $quantity);
$stmt->execute();

header("Location: ../dashboard.php?page=my_requests&success=1");
exit();
?>
