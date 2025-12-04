<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Validate category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid category ID!";
  header("Location: index.php");
  exit;
}

$categoryId = intval($_GET['id']);

$conn = connectDB();

// Step 1: Check if category is linked to any product
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM product WHERE category_id = ?");
$checkStmt->bind_param('i', $categoryId);
$checkStmt->execute();
$checkStmt->bind_result($productCount);
$checkStmt->fetch();
$checkStmt->close();

if ($productCount > 0) {
  $_SESSION['fail_message'] = "Cannot delete this category. It is connected to $productCount product(s).";
  header("Location: index.php");
  exit;
}

// Step 2: Delete category
$deleteStmt = $conn->prepare("DELETE FROM category WHERE id = ?");
if ($deleteStmt) {
  $deleteStmt->bind_param('i', $categoryId);

  if ($deleteStmt->execute()) {
    $_SESSION['success_message'] = "Category deleted successfully!";
  } else {
    $_SESSION['fail_message'] = "Failed to delete category!";
  }

  $deleteStmt->close();
} else {
  $_SESSION['fail_message'] = "Failed to prepare delete statement!";
}

$conn->close();

header("Location: index.php");
exit;
