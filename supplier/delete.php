<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Validate supplier ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid supplier ID!";
  header("Location: index.php");
  exit;
}

$supplierId = intval($_GET['id']);

$conn = connectDB();

// Check if supplier is linked to any product
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM product WHERE supplier_id = ?");
$checkStmt->bind_param('i', $supplierId);
$checkStmt->execute();
$checkStmt->bind_result($productCount);
$checkStmt->fetch();
$checkStmt->close();

if ($productCount > 0) {
  $_SESSION['fail_message'] = "Cannot delete this supplier. It is connected to $productCount product(s).";
  header("Location: index.php");
  exit;
}

// Step 2: Delete supplier
$deleteStmt = $conn->prepare("DELETE FROM supplier WHERE id = ?");
if ($deleteStmt) {
  $deleteStmt->bind_param('i', $supplierId);

  if ($deleteStmt->execute()) {
    $_SESSION['success_message'] = "Supplier deleted successfully!";
  } else {
    $_SESSION['fail_message'] = "Failed to delete supplier!";
  }

  $deleteStmt->close();
} else {
  $_SESSION['fail_message'] = "Failed to prepare delete statement!";
}

$conn->close();

header("Location: index.php");
exit;
