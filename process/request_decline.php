<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Only manager or admin can decline requests
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
   GET REQUEST INFO (prepared statement)
============================================================ */
$stmtReq = $conn->prepare("SELECT r.*, p.name AS product_name FROM requests r JOIN products p ON r.product_id = p.id WHERE r.id = ?");
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

$requester_id = $req['user_id'];
$product_name = $req['product_name'];
$quantity     = $req['quantity'];

/* ============================================================
   UPDATE STATUS
============================================================ */
$stmtUpd = $conn->prepare("UPDATE requests SET status = 'declined' WHERE id = ?");
$stmtUpd->bind_param("i", $req_id);
$update = $stmtUpd->execute();

/* ============================================================
   AUDIT LOG + NOTIFICATIONS (only if success)
============================================================ */
if ($update) {
    $actor_role = ucfirst($_SESSION['user']['role'] ?? 'manager');
    $actor_name = ($_SESSION['user']['username'] ?? 'Manager') . " ($actor_role)";

    logAction(
        $conn,
        $_SESSION['user']['id'],
        "DECLINE_REQUEST",
        "Declined request #$req_id for \"$product_name\" (Qty: $quantity)"
    );

    // Notify the staff member who made the request
    sendNotification(
        $conn,
        $requester_id,
        'staff',
        "❌ Your request for \"$product_name\" (Qty: $quantity) has been DECLINED by $actor_name.",
        'error'
    );

    // Notify admin
    sendNotification(
        $conn,
        null,
        'admin',
        "❌ Request #$req_id declined: \"$product_name\" (Qty: $quantity) by $actor_name",
        'info'
    );
}

header("Location: ../dashboard.php?page=requests&success=declined");
exit();
