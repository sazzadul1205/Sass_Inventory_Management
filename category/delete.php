<?php
// Include the auth guard to check user login and session
include_once __DIR__ . '/../config/auth_guard.php';

// Require permission to delete category
requirePermission('delete_category', '../index.php');

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Validate that a category ID is provided and is a number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid category ID!";
  header("Location: index.php");
  exit;
}

$categoryId = intval($_GET['id']);
$conn = connectDB();

// ---------------------------
// Step 1: Check for subcategories
// ---------------------------
// If this is a main category, do not allow deletion if subcategories exist
$stmtSub = $conn->prepare("SELECT COUNT(*) FROM category WHERE parent_id = ?");
$stmtSub->bind_param('i', $categoryId);
$stmtSub->execute();
$stmtSub->bind_result($subCount);
$stmtSub->fetch();
$stmtSub->close();

// If subcategories exist, block deletion
if ($subCount > 0) {
  $_SESSION['fail_message'] = "Cannot delete this category. It has $subCount subcategory(s).";
  header("Location: index.php");
  exit;
}

// ---------------------------
// Step 2: Check for linked products
// ---------------------------
// Prevent deletion if any products are associated with this category
$stmtProd = $conn->prepare("SELECT COUNT(*) FROM product WHERE category_id = ?");
$stmtProd->bind_param('i', $categoryId);
$stmtProd->execute();
$stmtProd->bind_result($productCount);
$stmtProd->fetch();
$stmtProd->close();

// If linked products exist, block deletion
if ($productCount > 0) {
  $_SESSION['fail_message'] = "Cannot delete this category. It is connected to $productCount product(s).";
  header("Location: index.php");
  exit;
}

// ---------------------------
// Step 3: Delete the category
// ---------------------------
// Safe to delete since there are no subcategories or linked products
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
  // If prepare fails, notify user
  $_SESSION['fail_message'] = "Failed to prepare delete statement!";
}

// Close DB connection
$conn->close();

// Redirect back to category list
header("Location: index.php");
exit;
