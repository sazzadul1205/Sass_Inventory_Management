<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'delete_user' permission
requirePermission('update_permissions', '../index.php');

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$response = ["status" => "fail"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['changes'])) {

    $changes = $_POST['changes'];
    $conn = connectDB();
    $success = true;

    foreach ($changes as $key => $value) {

        list($role_id, $perm_id) = explode('-', $key);

        //  BLOCK updates to admin (role 1)
        // if ((int)$role_id === 1) {
        //     $_SESSION['fail_message'] = "Admin permissions cannot be updated!";
        //     echo json_encode(["status" => "fail"]);
        //     exit;
        // }

        if ($value == 1) {
            $stmt = $conn->prepare("INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (?, ?)");
        } else {
            $stmt = $conn->prepare("DELETE FROM role_permission WHERE role_id=? AND permission_id=?");
        }

        if ($stmt) {
            $stmt->bind_param('ii', $role_id, $perm_id);

            if (!$stmt->execute()) {
                $success = false;
            }

            $stmt->close(); // safe now because it's inside the foreach
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

    $conn->close();
    echo json_encode($response);
    exit;
} else {
    $_SESSION['fail_message'] = "No changes detected!";
    echo json_encode($response);
    exit;
}
