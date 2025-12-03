<?php
include_once __DIR__ . '/../config/db_config.php';
session_start();

// Check for product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['fail_message'] = "Invalid product ID!";
  header("Location: index.php");
  exit;
}

$productId = intval($_GET['id']);
$conn = connectDB();

// Check stock before deletion
$stmtCheck = $conn->prepare("SELECT quantity_in_stock FROM product WHERE id = ?");
$stmtCheck->bind_param("i", $productId);
$stmtCheck->execute();
$stmtCheck->bind_result($quantity);
$stmtCheck->fetch();
$stmtCheck->close();

if ($quantity > 0) {
  $_SESSION['fail_message'] = "Cannot delete product with stock available!";
  header("Location: index.php");
  exit;
}

// Proceed to delete
$stmtDelete = $conn->prepare("DELETE FROM product WHERE id = ?");
$stmtDelete->bind_param("i", $productId);

if ($stmtDelete->execute()) {
  $_SESSION['success_message'] = "Product deleted successfully!";
} else {
  $_SESSION['fail_message'] = "Failed to delete product!";
}

$stmtDelete->close();
$conn->close();

// Redirect back to product listing
header("Location: index.php");
exit;
