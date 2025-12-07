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

// Initialize response
$response = ["status" => "fail"];

// Update permissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['changes'])) {
    $changes = $_POST['changes'];
    $conn = connectDB();
    $success = true;

    foreach ($changes as $key => $value) {
        list($role_id, $perm_id) = explode('-', $key);
        if ($role_id == 1) continue;

        if ($value == 1) {
            $stmt = $conn->prepare("INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (?, ?)");
        } else {
            $stmt = $conn->prepare("DELETE FROM role_permission WHERE role_id=? AND permission_id=?");
        }

        if ($stmt) {
            $stmt->bind_param('ii', $role_id, $perm_id);
            if (!$stmt->execute()) $success = false;
        } else {
            $success = false;
        }
    }

    if ($success) {
        $_SESSION['success_message'] = "Permissions updated successfully!";
        $response["status"] = "success";
    } else {
        $_SESSION['fail_message'] = "Failed to update permissions!";
    }
} else {
    $_SESSION['fail_message'] = "No changes detected!";
}

// Close the database connection
$stmt->close();
$conn->close();

echo json_encode($response);
exit;
