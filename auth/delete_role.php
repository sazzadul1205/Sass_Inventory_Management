<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['fail_message'] = "Invalid role ID!";
    header("Location: roles.php");
    exit;
}

$roleId = intval($_GET['id']);

// Prevent deletion of admin role (id = 1)
if ($roleId === 1) {
    $_SESSION['fail_message'] = "Cannot delete the admin role!";
    header("Location: roles.php");
    exit;
}

$conn = connectDB();
$success = true;

// Delete role-permission relationships first
$stmt = $conn->prepare("DELETE FROM role_permission WHERE role_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $roleId);
    if (!$stmt->execute()) $success = false;
    $stmt->close();
} else {
    $success = false;
}

// Delete role
if ($success) {
    $stmt = $conn->prepare("DELETE FROM role WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $roleId);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Role deleted successfully!";
        } else {
            $_SESSION['fail_message'] = "Failed to delete role!";
        }
        $stmt->close();
    } else {
        $_SESSION['fail_message'] = "Failed to prepare statement!";
    }
} else {
    $_SESSION['fail_message'] = "Failed to delete role permissions!";
}

header("Location: roles.php");
exit;
