<?php
// <!-- fetch_subcategories.php -->

include_once __DIR__ . '/../config/auth_guard.php';

$conn = connectDB(); // REQUIRED

$parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;

$stmt = $conn->prepare("
    SELECT id, name
    FROM category
    WHERE parent_id = ?
    ORDER BY name ASC
");
$stmt->bind_param("i", $parentId);
$stmt->execute();

$result = $stmt->get_result();

$subs = [];
while ($row = $result->fetch_assoc()) {
  $subs[] = $row;
}

header('Content-Type: application/json');
echo json_encode($subs);
