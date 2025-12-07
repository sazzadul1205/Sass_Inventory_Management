<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'delete_user' permission
// Unauthorized users will be redirected to index.php
requirePermission('delete_user', '../index.php');

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['fail_message'] = "Invalid user ID!";
    header("Location: users.php");
    exit;
}

// Get the user ID
$user_id = intval($_GET['id']);

// Connect to the database
$conn = connectDB();

// Prepare and execute the delete statement
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);

// Execute the delete statement
if ($stmt->execute()) {
    $_SESSION['success_message'] = "User deleted successfully!";
} else {
    $_SESSION['fail_message'] = "Failed to delete user!";
}

// Close the database connection
$stmt->close();
$conn->close();

// Redirect back to the users page
header("Location: users.php");
exit;
