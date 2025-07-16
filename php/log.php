<?php 
function log_admin_action($connect, $admin_id, $action, $details = null) {
    $stmt = $connect->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $action, $details);
    $stmt->execute();
}
?>