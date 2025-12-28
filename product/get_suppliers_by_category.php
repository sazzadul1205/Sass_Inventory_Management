<?php
// get_suppliers_by_category.php
include_once __DIR__ . '/../config/auth_guard.php';

$conn = connectDB();

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
  $category_id = intval($_GET['category_id']);

  // Get suppliers linked to the category
  $sql = "SELECT s.id, s.name 
            FROM supplier s
            INNER JOIN supplier_category sc ON s.id = sc.supplier_id
            WHERE sc.category_id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $category_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $suppliers = [];
  while ($row = $result->fetch_assoc()) {
    $suppliers[] = $row;
  }

  // Tell browser this is JSON
  header('Content-Type: application/json');

  echo json_encode($suppliers);
}
