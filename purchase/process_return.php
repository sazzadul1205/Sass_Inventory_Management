<?php
// process_return.php
session_start();
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../config/auth_guard.php';

// Check permissions
requirePermission('product_return', '../index.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['error_message'] = "Invalid request method.";
  header("Location: purchase_return.php");
  exit;
}

$conn = connectDB();

// Start transaction for data consistency
$conn->begin_transaction();

try {
  // Get form data with validation
  $lot_number = trim($_POST['lot_number'] ?? '');
  $purchase_id = (int)($_POST['purchase_id'] ?? 0);
  $product_id = (int)($_POST['product_id'] ?? 0);
  $return_quantity = (int)($_POST['return_quantity'] ?? 0);
  $refund_amount = (float)($_POST['refund_amount'] ?? 0);
  $return_reason = trim($_POST['return_reason'] ?? '');
  $refund_method = trim($_POST['refund_method'] ?? 'cash');
  $item_condition = trim($_POST['condition'] ?? 'new');
  $return_date = trim($_POST['return_date'] ?? date('Y-m-d'));
  $notes = trim($_POST['notes'] ?? '');
  $receipt_id = !empty($_POST['receipt_id']) ? (int)$_POST['receipt_id'] : null;
  $receipt_number = trim($_POST['receipt_number'] ?? '');
  $returned_by = (int)$_SESSION['user_id'];

  // Log received data
  error_log("=== PROCESS_RETURN.PHP DEBUG ===");
  error_log("Received POST data:");
  foreach ($_POST as $key => $value) {
    error_log("  $key: " . (is_array($value) ? print_r($value, true) : $value));
  }
  error_log("Session user_id: " . $_SESSION['user_id']);
  error_log("Returned by: " . $returned_by);

  // Validate required fields
  if (empty($lot_number) || $purchase_id <= 0 || $product_id <= 0 || $return_quantity <= 0) {
    throw new Exception("Invalid return data provided. Lot: $lot_number, Purchase ID: $purchase_id, Product ID: $product_id, Quantity: $return_quantity");
  }

  if (empty($return_reason)) {
    throw new Exception("Return reason is required.");
  }

  // 1. Get purchase details to verify
  $purchaseSql = "SELECT p.*, 
                    s.id as supplier_id,
                    pr.quantity_in_stock,
                    pr.name as product_name,
                    u.username as purchased_by_name
                    FROM purchase p
                    LEFT JOIN supplier s ON p.supplier_id = s.id
                    LEFT JOIN product pr ON p.product_id = pr.id
                    LEFT JOIN user u ON p.purchased_by = u.id
                    WHERE p.id = ? AND p.lot = ?";

  error_log("Purchase SQL: $purchaseSql");
  error_log("Purchase ID: $purchase_id, Lot: $lot_number");

  $stmt = $conn->prepare($purchaseSql);
  $stmt->bind_param("is", $purchase_id, $lot_number);
  $stmt->execute();
  $result = $stmt->get_result();
  $purchase = $result->fetch_assoc();

  if (!$purchase) {
    throw new Exception("Purchase not found or invalid lot number. Purchase ID: $purchase_id, Lot: $lot_number");
  }

  error_log("Purchase data found:");
  foreach ($purchase as $key => $value) {
    error_log("  $key: $value (type: " . gettype($value) . ")");
  }

  // Check supplier_id
  if (empty($purchase['supplier_id'])) {
    error_log("WARNING: supplier_id is empty in purchase data!");
    // Try to get it directly from purchase table
    $supplierCheckSql = "SELECT supplier_id FROM purchase WHERE id = ?";
    $supplierStmt = $conn->prepare($supplierCheckSql);
    $supplierStmt->bind_param("i", $purchase_id);
    $supplierStmt->execute();
    $supplierResult = $supplierStmt->get_result();
    $supplierData = $supplierResult->fetch_assoc();
    if ($supplierData && !empty($supplierData['supplier_id'])) {
      $purchase['supplier_id'] = $supplierData['supplier_id'];
      error_log("Got supplier_id from direct query: " . $purchase['supplier_id']);
    }
  }

  // 2. Validate return quantity
  if ($return_quantity > $purchase['product_left']) {
    throw new Exception("Cannot return more than available quantity. Available: " . $purchase['product_left'] . " units, Requested: $return_quantity");
  }

  // 3. Generate unique return number
  $timestamp = time();
  $random = rand(1000, 9999);
  $return_number = 'RET' . date('Ymd') . $timestamp . $random;
  error_log("Generated return number: $return_number");

  // 4. Insert return record
  $insertReturnSql = "INSERT INTO purchase_return 
                        (return_number, purchase_id, product_id, supplier_id, lot_number, 
                         receipt_id, receipt_number, return_quantity, unit_price, total_refund,
                         return_reason, refund_method, item_condition, return_date, notes, returned_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  error_log("Insert SQL: $insertReturnSql");

  $insertStmt = $conn->prepare($insertReturnSql);
  if (!$insertStmt) {
    throw new Exception("Failed to prepare insert statement: " . $conn->error);
  }

  $unit_price = (float)$purchase['purchase_price'];
  $total_refund = $unit_price * $return_quantity;

  error_log("Calculated values:");
  error_log("  Unit price: $unit_price");
  error_log("  Total refund: $total_refund");

  // Debug: List all variables to bind
  error_log("Variables to bind:");
  $variables = [
    'return_number' => $return_number,
    'purchase_id' => $purchase_id,
    'product_id' => $product_id,
    'supplier_id' => $purchase['supplier_id'],
    'lot_number' => $lot_number,
    'receipt_id' => $receipt_id,
    'receipt_number' => $receipt_number,
    'return_quantity' => $return_quantity,
    'unit_price' => $unit_price,
    'total_refund' => $total_refund,
    'return_reason' => $return_reason,
    'refund_method' => $refund_method,
    'item_condition' => $item_condition,
    'return_date' => $return_date,
    'notes' => $notes,
    'returned_by' => $returned_by
  ];

  foreach ($variables as $name => $value) {
    $type = gettype($value);
    $valueStr = ($value === null) ? 'NULL' : $value;
    error_log("  $name: $valueStr (type: $type)");
  }

  // Count the parameters
  $paramCount = count($variables);
  error_log("Total parameters to bind: $paramCount");

  // The issue might be in the format string. Let's build it dynamically:
  $formatString = "";
  foreach ($variables as $value) {
    if (is_int($value)) {
      $formatString .= "i";
    } elseif (is_float($value) || is_double($value)) {
      $formatString .= "d";
    } elseif ($value === null) {
      $formatString .= "s"; // NULL is bound as string
    } else {
      $formatString .= "s";
    }
  }

  error_log("Generated format string: $formatString");
  error_log("Format string length: " . strlen($formatString));

  // Bind parameters
  $bindResult = $insertStmt->bind_param(
    $formatString,
    $return_number,
    $purchase_id,
    $product_id,
    $purchase['supplier_id'],
    $lot_number,
    $receipt_id,
    $receipt_number,
    $return_quantity,
    $unit_price,
    $total_refund,
    $return_reason,
    $refund_method,
    $item_condition,
    $return_date,
    $notes,
    $returned_by
  );

  if (!$bindResult) {
    throw new Exception("Failed to bind parameters: " . $insertStmt->error);
  }

  error_log("Parameters bound successfully");

  if (!$insertStmt->execute()) {
    throw new Exception("Failed to create return record: " . $insertStmt->error);
  }

  $return_id = $conn->insert_id;
  error_log("Return record created with ID: $return_id");

  // 5. Update purchase product_left
  $updatePurchaseSql = "UPDATE purchase 
                          SET product_left = product_left - ?, 
                              updated_at = NOW()
                          WHERE id = ? AND lot = ?";
  $updateStmt = $conn->prepare($updatePurchaseSql);
  $updateStmt->bind_param("iis", $return_quantity, $purchase_id, $lot_number);

  if (!$updateStmt->execute()) {
    throw new Exception("Failed to update purchase record: " . $updateStmt->error);
  }

  error_log("Purchase updated successfully");

  // 6. Update product stock (reduce quantity_in_stock)
  $updateProductSql = "UPDATE product 
                         SET quantity_in_stock = quantity_in_stock - ?, 
                             updated_at = NOW()
                         WHERE id = ?";
  $productStmt = $conn->prepare($updateProductSql);
  $productStmt->bind_param("ii", $return_quantity, $product_id);

  if (!$productStmt->execute()) {
    throw new Exception("Failed to update product stock: " . $productStmt->error);
  }

  error_log("Product stock updated successfully");

  // 7. Create stock movement record (if stock_movement table exists)
  try {
    // Check if stock_movement table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'stock_movement'");
    if ($checkTable->num_rows > 0) {
      $insertMovementSql = "INSERT INTO stock_movement 
                                  (product_id, movement_type, quantity, reference_id, reference_type, 
                                   notes, movement_date, created_by)
                                  VALUES (?, 'return', -?, ?, 'purchase_return', ?, ?, ?)";

      $movementStmt = $conn->prepare($insertMovementSql);
      $movementNotes = "Purchase Return #$return_number - Product: " . $purchase['product_name'] .
        " - Lot: $lot_number - Reason: $return_reason";
      $movementStmt->bind_param(
        "iiissi",
        $product_id,
        $return_quantity,
        $return_id,
        $movementNotes,
        $return_date,
        $returned_by
      );

      $movementStmt->execute();
      error_log("Stock movement record created");
    }
  } catch (Exception $e) {
    // Continue even if stock movement fails (it's optional)
    error_log("Stock movement record failed: " . $e->getMessage());
  }

  // Commit transaction
  $conn->commit();
  error_log("Transaction committed successfully");

  // Log the action
  $logMessage = "Return processed - Return #: $return_number, Product: " . $purchase['product_name'] .
    ", Quantity: $return_quantity, Refund: $$total_refund, Lot: $lot_number";
  error_log($logMessage);

  // Success - redirect with success message
  $_SESSION['success_message'] = "✅ Return processed successfully!<br>
                                   <strong>Return Number:</strong> $return_number<br>
                                   <strong>Product:</strong> " . $purchase['product_name'] . "<br>
                                   <strong>Quantity:</strong> $return_quantity units<br>
                                   <strong>Refund Amount:</strong> $" . number_format($total_refund, 2);

  // Store return details for receipt page
  $_SESSION['last_return'] = [
    'return_id' => $return_id,
    'return_number' => $return_number,
    'product_name' => $purchase['product_name'],
    'quantity' => $return_quantity,
    'refund_amount' => $total_refund,
    'lot_number' => $lot_number,
    'supplier_id' => $purchase['supplier_id'],
    'return_date' => $return_date
  ];

  error_log("Redirecting to success page with return_id: $return_id");
  header("Location: return-list.php");
  exit;
} catch (Exception $e) {
  // Rollback transaction on error
  if (isset($conn) && method_exists($conn, 'rollback')) {
    $conn->rollback();
    error_log("Transaction rolled back due to error");
  }

  // Log error with full details
  error_log("Return processing error: " . $e->getMessage());
  error_log("Error trace: " . $e->getTraceAsString());

  // Store error in session and redirect back
  $_SESSION['error_message'] = "❌ Error processing return: " . $e->getMessage();

  $redirectUrl = "return-list.php";
  if (!empty($lot_number)) {
    $redirectUrl .= "?lot=" . urlencode($lot_number);
  }

  error_log("Redirecting to: $redirectUrl");
  header("Location: $redirectUrl");
  exit;
} finally {
  if (isset($conn)) {
    $conn->close();
    error_log("Database connection closed");
  }
  error_log("=== PROCESS_RETURN.PHP END ===");
}
