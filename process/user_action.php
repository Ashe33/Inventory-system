<?php
session_start();
include '../config/database.php';
include '../include/logger.php';
include '../include/notification.php';

/* ============================================================
   ROLE CHECK: Admin only
============================================================ */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$uid    = (int) ($_GET['id'] ?? 0);

if (!$uid || !$action) {
    header("Location: ../dashboard.php?page=users&error=invalid");
    exit();
}

// Prevent admin from acting on themselves
if ($uid === (int) $_SESSION['user']['id']) {
    header("Location: ../dashboard.php?page=users&error=self");
    exit();
}

/* ============================================================
   GET USER INFO (prepared statement)
============================================================ */
$stmtUser = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
$stmtUser->bind_param("i", $uid);
$stmtUser->execute();
$targetUser = $stmtUser->get_result()->fetch_assoc();

if (!$targetUser) {
    header("Location: ../dashboard.php?page=users&error=notfound");
    exit();
}

$username   = $targetUser['username'];
$actor_role = ucfirst($_SESSION['user']['role'] ?? 'admin');
$actor_name = ($_SESSION['user']['username'] ?? 'Admin') . " ($actor_role)";

switch ($action) {

    case 'approve':
        $stmtUpd = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmtUpd->bind_param("i", $uid);
        $stmtUpd->execute();

        logAction(
            $conn,
            $_SESSION['user']['id'],
            "APPROVE_USER",
            "Approved user: $username (ID: $uid)"
        );

        // Notify the user (by user_id)
        sendNotification(
            $conn,
            $uid,
            $targetUser['role'],
            "✅ Your account has been approved by $actor_name. You can now log in.",
            'success'
        );

        header("Location: ../dashboard.php?page=users&success=approved");
        break;

    case 'decline':
        $stmtUpd = $conn->prepare("UPDATE users SET status = 'declined' WHERE id = ?");
        $stmtUpd->bind_param("i", $uid);
        $stmtUpd->execute();

        logAction(
            $conn,
            $_SESSION['user']['id'],
            "DECLINE_USER",
            "Declined user: $username (ID: $uid)"
        );

        header("Location: ../dashboard.php?page=users&success=declined");
        break;

    case 'delete':
        $stmtDel = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->bind_param("i", $uid);
        $stmtDel->execute();

        logAction(
            $conn,
            $_SESSION['user']['id'],
            "DELETE_USER",
            "Deleted user: $username (ID: $uid)"
        );

        header("Location: ../dashboard.php?page=users&success=deleted");
        break;

    default:
        header("Location: ../dashboard.php?page=users&error=invalid");
        break;
}

exit();
