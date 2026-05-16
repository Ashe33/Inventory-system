<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Only manager or admin can add products
============================================================ */
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php?page=products");
    exit();
}

$name     = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$quantity = (int) ($_POST['quantity'] ?? 0);

if (empty($name) || $quantity < 0) {
    header("Location: ../dashboard.php?page=products&error=invalid");
    exit();
}

/* ============================================================
   INSERT PRODUCT
============================================================ */
$stmt = $conn->prepare("INSERT INTO products (name, quantity, category) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $name, $quantity, $category);
$insert = $stmt->execute();

/* ============================================================
   AUDIT LOG + NOTIFICATIONS (only if success)
============================================================ */
if ($insert) {
    $actor_id   = $_SESSION['user']['id'];
    $actor_role = ucfirst($_SESSION['user']['role'] ?? 'manager');
    $actor_name = ($_SESSION['user']['username'] ?? 'Manager') . " ($actor_role)";

    logAction(
        $conn,
        $actor_id,
        "ADD_PRODUCT",
        "Added product: $name (Category: $category, Qty: $quantity)"
    );

    // Notify admin role about new product
    sendNotification(
        $conn,
        null,
        'admin',
        "📦 New product added: \"$name\" (Category: $category, Qty: $quantity) by $actor_name",
        'info'
    );

    // Low stock warning notification if initial qty is already low
    if ($quantity <= 5) {
        sendNotification(
            $conn,
            null,
            'admin',
            "⚠️ Low stock alert: \"$name\" added with only $quantity units.",
            'warning'
        );
        sendNotification(
            $conn,
            null,
            'manager',
            "⚠️ Low stock alert: \"$name\" added with only $quantity units.",
            'warning'
        );
    }
}

header("Location: ../dashboard.php?page=products&success=added");
exit();
