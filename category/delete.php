<?php
include_once __DIR__ . '/../config/db_config.php';
session_start();

// Check for category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid category ID!";
  header("Location: index.php");
  exit;
}

$categoryId = intval($_GET['id']);
$conn = connectDB();

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
$stmt->bind_param("i", $categoryId);

if ($stmt->execute()) {
  $_SESSION['success_message'] = "Category deleted successfully!";
} else {
  $_SESSION['fail_message'] = "Failed to delete category!";
}

$stmt->close();
$conn->close();

// Redirect back to category listing
header("Location: index.php");
exit;
