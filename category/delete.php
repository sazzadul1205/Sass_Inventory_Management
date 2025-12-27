<?php
include_once __DIR__ . '/../config/auth_guard.php';
requirePermission('delete_category', '../index.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid category ID!";
  header("Location: index.php");
  exit;
}

$categoryId = intval($_GET['id']);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;

$conn = connectDB();

// Check for subcategories
$stmtSub = $conn->prepare("SELECT COUNT(*) FROM category WHERE parent_id = ?");
$stmtSub->bind_param('i', $categoryId);
$stmtSub->execute();
$stmtSub->bind_result($subCount);
$stmtSub->fetch();
$stmtSub->close();

if ($subCount > 0) {
  $_SESSION['fail_message'] = "Cannot delete this category. It has $subCount subcategory(s).";
  header("Location: index.php?page=$page");
  exit;
}

// Check for linked products
$stmtProd = $conn->prepare("SELECT COUNT(*) FROM product WHERE category_id = ?");
$stmtProd->bind_param('i', $categoryId);
$stmtProd->execute();
$stmtProd->bind_result($productCount);
$stmtProd->fetch();
$stmtProd->close();

if ($productCount > 0) {
  $_SESSION['fail_message'] = "Cannot delete this category. It is connected to $productCount product(s).";
  header("Location: index.php?page=$page");
  exit;
}

// Delete category
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

// Redirect back to the same page in pagination
header("Location: index.php?page=$page");
exit;
