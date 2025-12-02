<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['changes'])) {
    $changes = $_POST['changes'];
    $conn = connectDB();
    $success = true;

    foreach ($changes as $key => $value) {
        list($role_id, $perm_id) = explode('-', $key);
        if ($role_id == 1) continue; // skip admin

        if ($value == 1) {
            $stmt = $conn->prepare("INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (?, ?)");
        } else {
            $stmt = $conn->prepare("DELETE FROM role_permission WHERE role_id=? AND permission_id=?");
        }

        if ($stmt) {
            $stmt->bind_param('ii', $role_id, $perm_id);
            if (!$stmt->execute()) $success = false;
            $stmt->close();
        } else {
            $success = false;
        }
    }

    if ($success) {
        $_SESSION['success_message'] = "Permissions updated successfully!";
    } else {
        $_SESSION['fail_message'] = "Failed to update permissions!";
    }
} else {
    $_SESSION['fail_message'] = "No changes detected!";
}

// Redirect back to permissions page
header("Location: permissions.php");
exit;
