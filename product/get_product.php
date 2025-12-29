<?php
include_once __DIR__ . '/../config/auth_guard.php';
header('Content-Type: application/json');

// Silence warnings
error_reporting(E_ERROR | E_PARSE);

$conn = connectDB();

$productId = intval($_GET['id'] ?? 0);
if ($productId <= 0) {
  echo json_encode(['error' => 'Invalid product ID']);
  exit;
}

// Fetch product info
$sql = "
SELECT 
    p.*,
    c.name AS category_name,
    c.parent_id AS category_parent_id,
    s.name AS supplier_name
FROM product p
LEFT JOIN category c ON p.category_id = c.id
LEFT JOIN supplier s ON p.supplier_id = s.id
WHERE p.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
  echo json_encode(['error' => 'Product not found']);
  exit;
}

// Subcategory name from subcategory_id
$subcategory_name = '';
if (!empty($product['subcategory_id'])) {
  $stmt2 = $conn->prepare("SELECT name FROM category WHERE id = ?");
  $stmt2->bind_param("i", $product['subcategory_id']);
  $stmt2->execute();
  $res2 = $stmt2->get_result()->fetch_assoc();
  $subcategory_name = $res2['name'] ?? '';
}
$product['subcategory_name'] = $subcategory_name;

// Numeric conversion
$product['cost_price'] = floatval($product['cost_price']);
$product['selling_price'] = floatval($product['selling_price']);
$product['vat'] = floatval($product['vat']);
$product['price'] = floatval($product['price']);
$product['quantity_in_stock'] = intval($product['quantity_in_stock']);
$product['low_stock_limit'] = intval($product['low_stock_limit']);

// Return JSON
echo json_encode($product, JSON_UNESCAPED_UNICODE);
exit;
