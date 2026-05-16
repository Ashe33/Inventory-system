<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   AUTH CHECK: Must be logged in (any role can submit requests)
============================================================ */
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Only staff should submit requests (manager/admin manage them)
if (!in_array($_SESSION['user']['role'], ['staff'])) {
    header("Location: ../dashboard.php?page=home&error=forbidden");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard.php?page=my_requests");
    exit();
}

$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity   = (int) ($_POST['quantity'] ?? 0);
$user_id    = (int) $_SESSION['user']['id'];

if (!$product_id || $quantity <= 0) {
    header("Location: ../dashboard.php?page=my_requests&error=invalid");
    exit();
}

/* ============================================================
   CHECK PRODUCT EXISTS
============================================================ */
$stmtProd = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmtProd->bind_param("i", $product_id);
$stmtProd->execute();
$result = $stmtProd->get_result();

if ($result->num_rows === 0) {
    header("Location: ../dashboard.php?page=my_requests&error=notfound");
    exit();
}

$product = $result->fetch_assoc();

/* ============================================================
   CHECK STOCK
============================================================ */
if ($quantity > $product['quantity']) {
    header("Location: ../dashboard.php?page=my_requests&error=stock&max={$product['quantity']}");
    exit();
}

/* ============================================================
   INSERT REQUEST
============================================================ */
$stmt = $conn->prepare("INSERT INTO requests (user_id, product_id, quantity, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iii", $user_id, $product_id, $quantity);
$insert = $stmt->execute();

/* ============================================================
   AUDIT LOG + NOTIFICATIONS (only if success)
============================================================ */
if ($insert) {
    $requester_role = ucfirst($_SESSION['user']['role'] ?? 'staff');
    $requester_name = ($_SESSION['user']['username'] ?? 'Staff') . " ($requester_role)";
    $product_name   = $product['name'];

    logAction(
        $conn,
        $user_id,
        "REQUEST_SUBMIT",
        "Submitted request for \"$product_name\" (Qty: $quantity)"
    );

    // Notify managers of the new request
    sendNotification(
        $conn,
        null,
        'manager',
        "📋 New request from $requester_name: \"$product_name\" (Qty: $quantity) — awaiting approval.",
        'info'
    );

    // Notify admin as well
    sendNotification(
        $conn,
        null,
        'admin',
        "📋 New request from $requester_name: \"$product_name\" (Qty: $quantity) — awaiting approval.",
        'info'
    );

    header("Location: ../dashboard.php?page=my_requests&success=1");
    exit();
}

/* fallback */
header("Location: ../dashboard.php?page=my_requests&error=invalid");
exit();
