<?php
// get_products.php
include_once __DIR__ . '/../config/auth_guard.php';


$conn = connectDB();
header('Content-Type: application/json');

$supplierId = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;

if ($supplierId > 0) {
  // Get products for specific supplier
  $query = "SELECT p.id, p.name, p.price, p.quantity_in_stock, 
                     s.id AS supplier_id, s.name AS supplier_name
              FROM product p
              LEFT JOIN supplier s ON p.supplier_id = s.id
              WHERE p.supplier_id = ? AND p.quantity_in_stock > 0
              ORDER BY p.name ASC";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $supplierId);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  // Get all products
  $query = "SELECT p.id, p.name, p.price, p.quantity_in_stock, 
                     s.id AS supplier_id, s.name AS supplier_name
              FROM product p
              LEFT JOIN supplier s ON p.supplier_id = s.id
              WHERE p.quantity_in_stock > 0
              ORDER BY p.name ASC";

  $result = $conn->query($query);
}

$products = [];
while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}

echo json_encode($products);
$conn->close();
