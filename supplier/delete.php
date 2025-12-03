<?php
include_once __DIR__ . '/../config/db_config.php';
session_start();

// Check for supplier ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid supplier ID!";
  header("Location: index.php");
  exit;
}

$supplierId = intval($_GET['id']);
$conn = connectDB();

// Prepare and execute delete
$stmt = $conn->prepare("DELETE FROM supplier WHERE id = ?");
$stmt->bind_param("i", $supplierId);

if ($stmt->execute()) {
  $_SESSION['success_message'] = "Supplier deleted successfully!";
} else {
  $_SESSION['fail_message'] = "Failed to delete supplier!";
}

$stmt->close();
$conn->close();

// Redirect back to supplier listing
header("Location: index.php");
exit;
