<?php

function logAction($conn, $user_id, $action, $description = '') {
    $stmt = $conn->prepare("
        INSERT INTO audit_logs (user_id, action, description)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $user_id, $action, $description);
    $stmt->execute();
}
?>