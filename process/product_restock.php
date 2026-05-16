<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Only manager or admin can restock
============================================================ */
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

/* ============================================================
   GET PRODUCT INFO (for log)
============================================================ */
$stmt = $conn->prepare("SELECT name, quantity FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: ../dashboard.php?page=products&error=notfound");
    exit();
}

$productName = $product['name'];
$oldQty      = $product['quantity'];
$newQty      = $oldQty + $add_quantity;

/* ============================================================
   UPDATE STOCK
============================================================ */
$stmtUpd = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
$stmtUpd->bind_param("ii", $add_quantity, $id);
$update = $stmtUpd->execute();

/* ============================================================
   AUDIT LOG + NOTIFICATIONS (only if success)
============================================================ */
if ($update) {
    $actor_id   = $_SESSION['user']['id'];
    $actor_role = ucfirst($_SESSION['user']['role'] ?? 'manager');
    $actor_name = ($_SESSION['user']['username'] ?? 'Manager') . " ($actor_role)";

    logAction(
        $conn,
        $actor_id,
        "RESTOCK_PRODUCT",
        "Restocked product: $productName | Qty: $oldQty → $newQty (+$add_quantity)"
    );

    // Notify admin of restock
    sendNotification(
        $conn,
        null,
        'admin',
        "📦 Restock: \"$productName\" by $actor_name. Qty: $oldQty → $newQty (+$add_quantity)",
        'info'
    );
}

header("Location: ../dashboard.php?page=products&success=stocked");
exit();
