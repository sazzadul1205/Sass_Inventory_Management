<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'delete_user' permission
// Unauthorized users will be redirected to index.php
requirePermission('delete_user', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Check if role ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['fail_message'] = "Invalid role ID!";
    header("Location: roles.php");
    exit;
}

// Get the role ID
$roleId = intval($_GET['id']);

// Prevent deletion of admin role (id = 1)
if ($roleId === 1) {
    $_SESSION['fail_message'] = "Cannot delete the admin role!";
    header("Location: roles.php");
    exit;
}

// Connect to the database
$conn = connectDB();

// Initialize success flag
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

// Delete the role
if ($success) {
    $stmt = $conn->prepare("DELETE FROM role WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $roleId);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Role deleted successfully!";
        } else {
            $_SESSION['fail_message'] = "Failed to delete role!";
        }
    } else {
        $_SESSION['fail_message'] = "Failed to prepare statement!";
    }
} else {
    $_SESSION['fail_message'] = "Failed to delete role permissions!";
}

// Close the database connection
$stmt->close();
$conn->close();

// Redirect back to roles page
header("Location: roles.php");
exit;
