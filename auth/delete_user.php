<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$formError = "";
$conn = connectDB();

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['fail_message'] = "Invalid user ID!";
    header("Location: users.php");
    exit;
}

$user_id = $_GET['id'];

// Prepare and execute the delete statement
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "User deleted successfully!";
    header("Location: users.php");
} else {
    $_SESSION['fail_message'] = "Failed to delete user!";
    header("Location: users.php");
}

$stmt->close();
$conn->close();
