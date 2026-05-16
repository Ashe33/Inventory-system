<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Only manager or admin can edit products
============================================================ */
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

/* ============================================================
   GET OLD DATA (for audit log)
============================================================ */
$stmtOld = $conn->prepare("SELECT name, quantity, category FROM products WHERE id = ?");
$stmtOld->bind_param("i", $id);
$stmtOld->execute();
$old = $stmtOld->get_result()->fetch_assoc();

if (!$old) {
    header("Location: ../dashboard.php?page=products&error=notfound");
    exit();
}

/* ============================================================
   UPDATE PRODUCT
============================================================ */
$stmt = $conn->prepare("UPDATE products SET name=?, quantity=?, category=? WHERE id=?");
$stmt->bind_param("sisi", $name, $quantity, $category, $id);
$update = $stmt->execute();

/* ============================================================
   AUDIT LOG + NOTIFICATIONS (only if success)
============================================================ */
if ($update) {
    $actor_id   = $_SESSION['user']['id'];
    $actor_role = ucfirst($_SESSION['user']['role'] ?? 'manager');
    $actor_name = ($_SESSION['user']['username'] ?? 'Manager') . " ($actor_role)";
    $oldName    = $old['name'] ?? 'Unknown';
    $oldQty     = $old['quantity'] ?? 0;
    $oldCat     = $old['category'] ?? 'None';

    logAction(
        $conn,
        $actor_id,
        "UPDATE_PRODUCT",
        "Updated product [$oldName → $name], Category [$oldCat → $category], Qty [$oldQty → $quantity]"
    );

    // Notify admin of product update
    sendNotification(
        $conn,
        null,
        'admin',
        "✏️ Product updated: \"$name\" (Qty: $oldQty → $quantity) by $actor_name",
        'info'
    );

    // Low stock warning if new quantity is low
    if ($quantity <= 5) {
        sendNotification(
            $conn,
            null,
            'admin',
            "⚠️ Low stock alert: \"$name\" now has only $quantity units.",
            'warning'
        );
        sendNotification(
            $conn,
            null,
            'manager',
            "⚠️ Low stock alert: \"$name\" now has only $quantity units.",
            'warning'
        );
    }
}

header("Location: ../dashboard.php?page=products&success=updated");
exit();
