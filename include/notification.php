<?php
/* ============================================================
   sendNotification()
   
   Parameters:
   - $conn      : mysqli connection
   - $user_id   : int|null — specific user, or NULL for role-broadcast
   - $role      : string   — 'admin'|'manager'|'staff' (required for role-broadcast, or for tagging)
   - $message   : string   — notification text
   - $type      : string   — 'info'|'success'|'warning'|'error'
   
   Logic:
   - If $user_id is set: personal notification (visible to that user only)
   - If $user_id is null: role-broadcast (visible to all users with that role)
============================================================ */
function sendNotification($conn, $user_id, $role, $message, $type = 'info') {

    // Sanitize type
    $allowed_types = ['info', 'success', 'warning', 'error'];
    if (!in_array($type, $allowed_types)) {
        $type = 'info';
    }

    // Sanitize role
    $allowed_roles = ['admin', 'manager', 'staff'];
    if (!in_array($role, $allowed_roles)) {
        $role = 'staff';
    }

    if ($user_id !== null) {
        // Personal notification
        $uid  = (int) $user_id;
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, role, message, type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $uid, $role, $message, $type);
    } else {
        // Role-broadcast notification (user_id = NULL)
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, role, message, type)
            VALUES (NULL, ?, ?, ?)
        ");
        $stmt->bind_param("sss", $role, $message, $type);
    }

    return $stmt->execute();
}
?>
