<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Only manager or admin can approve requests
============================================================ */
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$req_id = (int) ($_GET['id'] ?? 0);

if (!$req_id) {
    header("Location: ../dashboard.php?page=requests&error=invalid");
    exit();
}

/* ============================================================
   GET REQUEST DATA (using prepared statement)
============================================================ */
$stmtReq = $conn->prepare("SELECT * FROM requests WHERE id = ?");
$stmtReq->bind_param("i", $req_id);
$stmtReq->execute();
$req = $stmtReq->get_result()->fetch_assoc();

if (!$req) {
    header("Location: ../dashboard.php?page=requests&error=invalid");
    exit();
}

if ($req['status'] !== 'pending') {
    header("Location: ../dashboard.php?page=requests&error=already_processed");
    exit();
}

$product_id   = $req['product_id'];
$quantity     = $req['quantity'];
$requester_id = $req['user_id'];

/* ============================================================
   GET PRODUCT DATA
============================================================ */
$stmtProd = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmtProd->bind_param("i", $product_id);
$stmtProd->execute();
$product = $stmtProd->get_result()->fetch_assoc();

if (!$product) {
    header("Location: ../dashboard.php?page=requests&error=invalid");
    exit();
}

if ($product['quantity'] < $quantity) {
    header("Location: ../dashboard.php?page=requests&error=stock");
    exit();
}

$oldStock    = $product['quantity'];
$newStock    = $oldStock - $quantity;
$productName = $product['name'];

/* ============================================================
   UPDATE STOCK
============================================================ */
$stmtStock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
$stmtStock->bind_param("ii", $quantity, $product_id);
$stmtStock->execute();

/* ============================================================
   UPDATE REQUEST STATUS
============================================================ */
$stmtStatus = $conn->prepare("UPDATE requests SET status = 'approved' WHERE id = ?");
$stmtStatus->bind_param("i", $req_id);
$stmtStatus->execute();

/* ============================================================
   AUDIT LOG
============================================================ */
$actor_role = ucfirst($_SESSION['user']['role'] ?? 'manager');
$actor_name = ($_SESSION['user']['username'] ?? 'Manager') . " ($actor_role)";
logAction(
    $conn,
    $_SESSION['user']['id'],
    "APPROVE_REQUEST",
    "Approved request #$req_id for \"$productName\" (Qty: $quantity) | Stock: $oldStock → $newStock"
);

/* ============================================================
   NOTIFICATIONS
   - Notify the staff member who made the request
   - Notify admin
   - Low stock warning if stock falls low
============================================================ */

// Notify the staff member
sendNotification(
    $conn,
    $requester_id,
    'staff',
    "✅ Your request for \"$productName\" (Qty: $quantity) has been APPROVED by $actor_name.",
    'success'
);

// Notify admin
sendNotification(
    $conn,
    null,
    'admin',
    "✅ Request #$req_id approved: \"$productName\" (Qty: $quantity) by $actor_name. Stock: $oldStock → $newStock",
    'info'
);

// Low stock warning if stock after approval is low
if ($newStock <= 5) {
    sendNotification(
        $conn,
        null,
        'admin',
        "⚠️ Low stock alert: \"$productName\" now has only $newStock units remaining.",
        'warning'
    );
    sendNotification(
        $conn,
        null,
        'manager',
        "⚠️ Low stock alert: \"$productName\" now has only $newStock units remaining.",
        'warning'
    );
}

header("Location: ../dashboard.php?page=requests&success=approved");
exit();
