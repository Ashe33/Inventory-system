<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$req_id = (int) ($_GET['id'] ?? 0);

if (!$req_id) {
    header("Location: ../dashboard.php?page=requests&error=invalid");
    exit();
}

$req = $conn->query("SELECT * FROM requests WHERE id=$req_id")->fetch_assoc();

if ($req && $req['status'] === 'pending') {
    $product_id = $req['product_id'];
    $quantity   = $req['quantity'];

    $product = $conn->query("SELECT * FROM products WHERE id=$product_id")->fetch_assoc();

    if ($product && $product['quantity'] >= $quantity) {
        $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE id=$product_id");
        $conn->query("UPDATE requests SET status='approved' WHERE id=$req_id");
        header("Location: ../dashboard.php?page=requests&success=approved");
        exit();
    } else {
        header("Location: ../dashboard.php?page=requests&error=stock");
        exit();
    }
}

header("Location: ../dashboard.php?page=requests&error=invalid");
exit();
?>
