<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Manager or admin can delete products
============================================================ */
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);

if (!$id) {
    header("Location: ../dashboard.php?page=products&error=invalid");
    exit();
}

/* ============================================================
   GET PRODUCT INFO (for log and notification)
============================================================ */
$stmt = $conn->prepare("SELECT name, category, quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: ../dashboard.php?page=products&error=notfound");
    exit();
}

$productName = $product['name'];
$productQty  = $product['quantity'];

/* ============================================================
   DELETE PRODUCT
============================================================ */
$stmtDel = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmtDel->bind_param("i", $id);
$delete = $stmtDel->execute();

/* ============================================================
   AUDIT LOG + NOTIFICATIONS (only if success)
============================================================ */
if ($delete) {
    $actor_id   = $_SESSION['user']['id'];
    $actor_role = ucfirst($_SESSION['user']['role'] ?? 'manager');
    $actor_name = ($_SESSION['user']['username'] ?? 'Manager') . " ($actor_role)";

    logAction(
        $conn,
        $actor_id,
        "DELETE_PRODUCT",
        "Deleted product: $productName (ID: $id, Qty was: $productQty)"
    );

    // Notify admin of product deletion
    sendNotification(
        $conn,
        null,
        'admin',
        "🗑️ Product deleted: \"$productName\" by $actor_name",
        'warning'
    );
}

header("Location: ../dashboard.php?page=products&success=deleted");
exit();
