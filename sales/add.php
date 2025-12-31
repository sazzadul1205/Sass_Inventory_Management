<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'add_sale' permission
requirePermission('add_sale', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();
$soldBy = (int)$_SESSION['user_id'];

/* ---- AJAX HANDLERS ---- */
if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  /* Fetch Products with Lots */
  if ($_GET['action'] === 'fetch_products_with_lots') {
    $productId = (int)($_GET['product_id'] ?? 0);

    if (!$productId) {
      echo json_encode(['success' => false, 'error' => 'Product ID required']);
      exit;
    }

    // Check if purchase table has product_left column
    $checkColumn = $conn->query("SHOW COLUMNS FROM purchase LIKE 'product_left'");
    $hasProductLeft = $checkColumn->num_rows > 0;

    if (!$hasProductLeft) {
      // Fallback to using quantity column
      $stmt = $conn->prepare("
                SELECT 
                    p.id, 
                    p.name, 
                    p.selling_price, 
                    p.vat,
                    p.quantity_in_stock,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(pur.lot, '::', pur.quantity, '::', pur.purchase_price) 
                            SEPARATOR '||'
                        )
                        FROM purchase pur
                        WHERE pur.product_id = p.id 
                            AND pur.quantity > 0
                        ORDER BY pur.purchase_date ASC
                    ) as lots_info
                FROM product p
                WHERE p.id = ? AND p.status = 'active'
            ");
    } else {
      // Use product_left column
      $stmt = $conn->prepare("
                SELECT 
                    p.id, 
                    p.name, 
                    p.selling_price, 
                    p.vat,
                    p.quantity_in_stock,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(pur.lot, '::', pur.product_left, '::', pur.purchase_price) 
                            SEPARATOR '||'
                        )
                        FROM purchase pur
                        WHERE pur.product_id = p.id 
                            AND pur.product_left > 0
                        ORDER BY pur.purchase_date ASC
                    ) as lots_info
                FROM product p
                WHERE p.id = ? AND p.status = 'active'
            ");
    }

    $stmt->bind_param('i', $productId);

    if (!$stmt->execute()) {
      echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
      exit;
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      echo json_encode(['success' => false, 'error' => 'Product not found']);
      exit;
    }

    $product = $result->fetch_assoc();

    // Parse lots information
    $lots = [];
    if (!empty($product['lots_info'])) {
      $lotsData = explode('||', $product['lots_info']);
      foreach ($lotsData as $lotData) {
        list($lot, $quantity, $purchasePrice) = explode('::', $lotData);
        $lots[] = [
          'lot' => $lot,
          'quantity' => (int)$quantity,
          'purchase_price' => (float)$purchasePrice
        ];
      }
    }

    echo json_encode([
      'success' => true,
      'product' => [
        'id' => $product['id'],
        'name' => $product['name'],
        'selling_price' => (float)$product['selling_price'],
        'vat' => (float)$product['vat'],
        'quantity_in_stock' => (int)$product['quantity_in_stock']
      ],
      'lots' => $lots,
      'debug' => ['has_product_left' => $hasProductLeft]
    ]);
    exit;
  }

  /* Submit Sale with Detailed Debugging */
  if ($_GET['action'] === 'submit_sale') {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Log the incoming data
    error_log("=== SALE SUBMISSION START ===");
    error_log("Raw POST data: " . $rawData);
    error_log("Decoded data: " . print_r($data, true));

    if (json_last_error() !== JSON_ERROR_NONE) {
      error_log("JSON decode error: " . json_last_error_msg());
      echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON data',
        'debug' => [
          'json_error' => json_last_error_msg(),
          'raw_data' => $rawData
        ]
      ]);
      exit;
    }

    $buyerName = trim($data['buyer_name'] ?? '');
    $buyerPhone = trim($data['buyer_phone'] ?? '');
    $saleDate = $data['sale_date'] ?? date('Y-m-d');
    $items = $data['items'] ?? [];
    $discountPercent = floatval($data['discount_percent'] ?? 0);

    error_log("Buyer: $buyerName, Phone: $buyerPhone, Date: $saleDate, Items count: " . count($items));

    if (empty($buyerName) || empty($items)) {
      $error = 'Invalid data - buyer name or items missing';
      error_log($error);
      echo json_encode([
        'success' => false,
        'error' => $error,
        'debug' => ['buyer_name' => $buyerName, 'items_count' => count($items)]
      ]);
      exit;
    }

    // Validate all items have required data
    $invalidItems = [];
    foreach ($items as $index => $i) {
      if (empty($i['product_id']) || empty($i['lot']) || empty($i['quantity']) || empty($i['selling_price'])) {
        $invalidItems[] = ['index' => $index, 'item' => $i];
      }
    }

    if (!empty($invalidItems)) {
      error_log("Invalid items found: " . print_r($invalidItems, true));
      echo json_encode([
        'success' => false,
        'error' => 'Invalid item data',
        'debug' => ['invalid_items' => $invalidItems]
      ]);
      exit;
    }

    // Calculate total including VAT
    $totalAmount = 0;
    foreach ($items as $i) {
      $qty = (int)$i['quantity'];
      $price = (float)$i['selling_price'];
      $vatPercent = (float)($i['vat_percent'] ?? 0);
      $subtotal = $qty * $price;
      $totalAmount += $subtotal + ($subtotal * $vatPercent / 100);
    }

    $discountValue = $totalAmount * ($discountPercent / 100);

    error_log("Calculated total: $totalAmount, Discount: $discountValue");

    $conn->begin_transaction();
    try {
      // Check if columns exist in sale table
      $checkColumns = $conn->query("SHOW COLUMNS FROM sale");
      $saleColumns = [];
      while ($col = $checkColumns->fetch_assoc()) {
        $saleColumns[] = $col['Field'];
      }

      error_log("Sale table columns: " . print_r($saleColumns, true));

      // Generate receipt number
      $receiptNumber = 'SALE-' . date('Ymd') . '-' . str_pad($soldBy, 3, '0', STR_PAD_LEFT) . '-' . substr(md5(uniqid()), 0, 6);

      error_log("Generated receipt number: $receiptNumber");

      // Insert receipt
      $stmtReceipt = $conn->prepare("
                INSERT INTO receipt
                (receipt_number, type, total_amount, discount_value, created_by, created_at, updated_at)
                VALUES (?, 'sale', ?, ?, ?, NOW(), NOW())
            ");

      if (!$stmtReceipt) {
        throw new Exception("Failed to prepare receipt statement: " . $conn->error);
      }

      $stmtReceipt->bind_param("sdii", $receiptNumber, $totalAmount, $discountValue, $soldBy);

      error_log("Executing receipt insert...");

      if (!$stmtReceipt->execute()) {
        throw new Exception("Failed to create receipt: " . $conn->error . " | SQL: " . $conn->info);
      }

      $receiptId = $stmtReceipt->insert_id;
      $stmtReceipt->close();

      error_log("Receipt created with ID: $receiptId");

      // Check which columns we have and build appropriate SQL
      $hasLotColumn = in_array('lot', $saleColumns);
      $hasVatPercentColumn = in_array('vat_percent', $saleColumns);
      $hasBuyerIdColumn = in_array('buyer_id', $saleColumns);
      $hasBuyerNameColumn = in_array('buyer_name', $saleColumns);
      $hasBuyerPhoneColumn = in_array('buyer_phone', $saleColumns);

      // Build sale insert SQL based on available columns
      $saleColumnsSql = "receipt_id, product_id, quantity, sale_price, sale_date, sold_by, created_at, updated_at";
      $saleValuesSql = "?, ?, ?, ?, ?, ?, NOW(), NOW()";
      $saleBindTypes = "iiidsi";
      $saleBindValues = [$receiptId];

      if ($hasLotColumn) {
        $saleColumnsSql .= ", lot";
        $saleValuesSql .= ", ?";
        $saleBindTypes .= "s";
      }

      if ($hasVatPercentColumn) {
        $saleColumnsSql .= ", vat_percent";
        $saleValuesSql .= ", ?";
        $saleBindTypes .= "d";
      }

      if ($hasBuyerIdColumn) {
        $saleColumnsSql .= ", buyer_id";
        $saleValuesSql .= ", ?";
        $saleBindTypes .= "s";
        // Generate simple buyer ID
        $buyerId = 'B' . date('Ymd') . str_pad($soldBy, 3, '0', STR_PAD_LEFT) . substr(md5(uniqid()), 0, 4);
      }

      if ($hasBuyerNameColumn) {
        $saleColumnsSql .= ", buyer_name";
        $saleValuesSql .= ", ?";
        $saleBindTypes .= "s";
      }

      if ($hasBuyerPhoneColumn) {
        $saleColumnsSql .= ", buyer_phone";
        $saleValuesSql .= ", ?";
        $saleBindTypes .= "s";
      }

      $saleInsertSql = "INSERT INTO sale ($saleColumnsSql) VALUES ($saleValuesSql)";
      error_log("Sale insert SQL: $saleInsertSql");
      error_log("Bind types: $saleBindTypes");

      $stmtSale = $conn->prepare($saleInsertSql);
      if (!$stmtSale) {
        throw new Exception("Failed to prepare sale statement: " . $conn->error);
      }

      // Check if purchase table has product_left column
      $checkPurchase = $conn->query("SHOW COLUMNS FROM purchase LIKE 'product_left'");
      $hasProductLeft = $checkPurchase->num_rows > 0;

      if ($hasProductLeft) {
        $purchaseUpdateSql = "UPDATE purchase SET product_left = product_left - ? WHERE product_id = ? AND lot = ? AND product_left >= ?";
      } else {
        $purchaseUpdateSql = "UPDATE purchase SET quantity = quantity - ? WHERE product_id = ? AND lot = ? AND quantity >= ?";
      }

      error_log("Purchase update SQL: $purchaseUpdateSql");

      $stmtUpdatePurchase = $conn->prepare($purchaseUpdateSql);
      if (!$stmtUpdatePurchase) {
        throw new Exception("Failed to prepare purchase update: " . $conn->error);
      }

      $stmtUpdateProduct = $conn->prepare("
                UPDATE product 
                SET quantity_in_stock = quantity_in_stock - ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

      if (!$stmtUpdateProduct) {
        throw new Exception("Failed to prepare product update: " . $conn->error);
      }

      foreach ($items as $index => $item) {
        error_log("Processing item $index: " . print_r($item, true));

        $productId = (int)$item['product_id'];
        $lot = $item['lot'];
        $quantity = (int)$item['quantity'];
        $sellingPrice = (float)$item['selling_price'];
        $vatPercent = (float)($item['vat_percent'] ?? 0);

        // Build bind parameters for sale insert
        $bindValues = [$receiptId, $productId, $quantity, $sellingPrice, $saleDate, $soldBy];

        if ($hasLotColumn) {
          $bindValues[] = $lot;
        }

        if ($hasVatPercentColumn) {
          $bindValues[] = $vatPercent;
        }

        if ($hasBuyerIdColumn) {
          $bindValues[] = $buyerId;
        }

        if ($hasBuyerNameColumn) {
          $bindValues[] = $buyerName;
        }

        if ($hasBuyerPhoneColumn) {
          $bindValues[] = $buyerPhone;
        }

        // Bind sale parameters
        $stmtSale->bind_param($saleBindTypes, ...$bindValues);

        error_log("Executing sale insert for product $productId...");

        if (!$stmtSale->execute()) {
          throw new Exception("Failed to insert sale for product ID $productId: " . $stmtSale->error);
        }

        error_log("Sale record inserted");

        // Update purchase stock
        $stmtUpdatePurchase->bind_param("issi", $quantity, $productId, $lot, $quantity);

        error_log("Executing purchase update for lot $lot...");

        if (!$stmtUpdatePurchase->execute()) {
          throw new Exception("Failed to update purchase stock for lot $lot: " . $stmtUpdatePurchase->error);
        }

        $affectedRows = $stmtUpdatePurchase->affected_rows;
        error_log("Purchase update affected rows: $affectedRows");

        if ($affectedRows == 0) {
          throw new Exception("Not enough stock in lot $lot for product ID $productId. Available: " .
            ($hasProductLeft ? "product_left column" : "quantity column"));
        }

        // Update product total stock
        $stmtUpdateProduct->bind_param("ii", $quantity, $productId);

        error_log("Executing product update...");

        if (!$stmtUpdateProduct->execute()) {
          throw new Exception("Failed to update product stock for ID $productId: " . $stmtUpdateProduct->error);
        }

        error_log("Product stock updated");
      }

      $stmtSale->close();
      $stmtUpdatePurchase->close();
      $stmtUpdateProduct->close();

      // Commit transaction
      error_log("Committing transaction...");
      $conn->commit();
      error_log("Transaction committed successfully");

      echo json_encode([
        'success' => true,
        'receipt_id' => $receiptId,
        'buyer_id' => $buyerId ?? 'N/A',
        'message' => 'Sale completed successfully',
        'debug' => [
          'receipt_number' => $receiptNumber,
          'total_amount' => $totalAmount,
          'items_count' => count($items),
          'columns_found' => [
            'lot' => $hasLotColumn,
            'vat_percent' => $hasVatPercentColumn,
            'buyer_id' => $hasBuyerIdColumn,
            'buyer_name' => $hasBuyerNameColumn,
            'buyer_phone' => $hasBuyerPhoneColumn,
            'product_left' => $hasProductLeft
          ]
        ]
      ]);
      exit;
    } catch (Exception $e) {
      // Rollback transaction on error
      error_log("ROLLING BACK TRANSACTION: " . $e->getMessage());
      $conn->rollback();

      // Get MySQL error if any
      $mysqlError = $conn->error;
      error_log("MySQL Error: $mysqlError");

      echo json_encode([
        'success' => false,
        'error' => 'Failed to process sale',
        'debug' => [
          'exception_message' => $e->getMessage(),
          'exception_trace' => $e->getTraceAsString(),
          'mysql_error' => $mysqlError,
          'mysql_errno' => $conn->errno,
          'items_data' => $items,
          'sold_by' => $soldBy,
          'buyer_name' => $buyerName
        ]
      ]);
      exit;
    }
  }
}

/* ---- Fetch all active products for initial dropdown ---- */
$products = $conn->query("
  SELECT p.id, p.name, p.quantity_in_stock
  FROM product p
  WHERE p.status = 'active' AND p.quantity_in_stock > 0
  ORDER BY p.name ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Sale | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Mobile + Theme -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="light dark" />

  <!-- Fonts -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    media="print" onload="this.media='all'" />

  <!-- Bootstrap Icons -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

  <!-- AdminLTE (Core Theme) -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />

  <!-- DataTables -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <style>
    .sale-item-row {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #dee2e6;
      position: relative;
    }

    .row-number {
      position: absolute;
      top: 10px;
      left: 10px;
      background: #007bff;
      color: white;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    .remove-row-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      width: 30px;
      height: 30px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .total-value {
      font-weight: bold;
      font-size: 1.1rem;
      color: #198754;
      background-color: #d4edda;
      padding: 10px;
      border-radius: 4px;
      text-align: center;
    }

    .products-container {
      max-height: 500px;
      overflow-y: auto;
      padding-right: 10px;
    }

    .lot-info {
      font-size: 0.85rem;
      color: #6c757d;
      margin-top: 5px;
    }

    .lot-amount {
      color: #198754;
      font-weight: bold;
    }

    .summary-box {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #e9ecef;
    }

    .summary-row:last-child {
      border-bottom: none;
      font-weight: bold;
      font-size: 1.2rem;
      color: #198754;
    }

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
      height: calc(1.5em + 0.75rem + 2px);
      /* match bootstrap input height */
      padding: 0.375rem 0.75rem;
      line-height: 1.5;
      border-radius: 8px;
      border: 1px solid #ced4da;
      background-color: #fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
      color: #000;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border-color: #000 transparent transparent transparent;
    }

    .select2-container--default .select2-dropdown {
      background-color: #fff;
      color: #000;
    }

    .select2-container--default .select2-results__option {
      color: #000;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      background-color: #fff;
      color: #000;
      line-height: 1.5;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Navbar -->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- App Main -->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0" style="font-weight: 800;">Add New Sale</h3>
        </div>
      </div>

      <!-- App Content Body -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body">
              <!-- Header -->
              <h4 class="fw-bold text-secondary border-bottom pb-2">
                Add Sale Information
              </h4>

              <!-- Alert -->
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Once a sale is created, it cannot be edited.
              </div>

              <!-- Buyer Info & Sale Date -->
              <div class="row mb-4">
                <!-- Buyer Name -->
                <div class="col-md-4">
                  <label for="buyer_name" class="form-label">
                    <i class="bi bi-person me-1"></i>Buyer Name
                  </label>
                  <input
                    type="text"
                    id="buyer_name"
                    class="form-control"
                    placeholder="Enter buyer name"
                    required>
                </div>

                <!-- Buyer Phone -->
                <div class="col-md-4">
                  <label for="buyer_phone" class="form-label">
                    <i class="bi bi-telephone me-1"></i>Phone Number
                  </label>
                  <input
                    type="tel"
                    id="buyer_phone"
                    class="form-control"
                    placeholder="01XXXXXXXXX"
                    pattern="[0-9]{10,15}">
                </div>

                <!-- Sale Date -->
                <div class="col-md-4">
                  <label for="sale_date" class="form-label">
                    <i class="bi bi-calendar-date me-1"></i>Sale Date
                  </label>
                  <input
                    type="date"
                    id="sale_date"
                    class="form-control"
                    value="<?= date('Y-m-d') ?>"
                    required>
                </div>
              </div>

              <!-- Sale Items -->
              <div id="saleItemsContainer" class="products-container"></div>

              <!-- Add Product Button -->
              <div class="mt-3 mb-4">
                <button type="button" class="btn btn-success" onclick="addNewRow()">
                  <i class="bi bi-plus-circle me-1"></i>Add Product
                </button>
              </div>

              <!-- Discount & Summary -->
              <div class="row mt-4">
                <div class="col-md-6">
                  <label for="discount_percent" class="form-label">
                    <i class="bi bi-percent me-1"></i>Discount (%)
                  </label>
                  <input
                    type="number"
                    id="discount_percent"
                    class="form-control"
                    value="0"
                    min="0"
                    max="100"
                    step="0.01"
                    oninput="updateSummary()">
                </div>
                <div class="col-md-6">
                  <div class="summary-box">
                    <div class="summary-row">
                      <span>Total Price:</span>
                      <span id="total_price">$0.00</span>
                    </div>
                    <div class="summary-row">
                      <span>Discount:</span>
                      <span id="discount_amount">$0.00</span>
                    </div>
                    <div class="summary-row">
                      <span>Final Price:</span>
                      <span id="final_price" class="text-success">$0.00</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Save & Cancel Buttons -->
              <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-primary" onclick="submitSale()">
                  <i class="bi bi-cart-check me-1"></i> Save Sale
                </button>
                <a href="index.php" class="btn btn-secondary px-4 py-2">
                  <i class="bi bi-x-circle"></i> Cancel
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    let rowCounter = 1;

    function formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(amount);
    }

    function generateId() {
      return 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    }

    function fetchProductLots(productId, rowId) {
      if (!productId) {
        resetRow(rowId);
        return;
      }

      fetch(`?action=fetch_products_with_lots&product_id=${productId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            updateRowWithProductData(rowId, data);
          } else {
            alert('Error loading product data: ' + data.error);
            resetRow(rowId);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          resetRow(rowId);
        });
    }

    function updateRowWithProductData(rowId, data) {
      const row = document.getElementById(rowId);
      const product = data.product;
      const lots = data.lots;

      // Update selling price and VAT
      row.querySelector('.price-input').value = parseFloat(product.selling_price).toFixed(2);
      row.querySelector('.vat-input').value = product.vat;

      // Update stock display
      row.querySelector('.stock-info').textContent = `Available Stock: ${product.quantity_in_stock}`;

      // Populate lot dropdown
      const lotSelect = row.querySelector('.lot-select');
      lotSelect.innerHTML = '<option value="">-- Select Lot --</option>';

      if (lots.length > 0) {
        lots.forEach(lot => {
          const option = document.createElement('option');
          option.value = lot.lot;
          option.textContent = `${lot.lot} (${lot.quantity} available)`;
          option.dataset.quantity = lot.quantity;
          option.dataset.purchasePrice = lot.purchase_price;
          lotSelect.appendChild(option);
        });
        lotSelect.disabled = false;
      } else {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No lots available';
        lotSelect.appendChild(option);
        lotSelect.disabled = true;
      }

      calculateRowTotal(rowId);
    }

    function resetRow(rowId) {
      const row = document.getElementById(rowId);
      row.querySelector('.price-input').value = '0.00';
      row.querySelector('.vat-input').value = '0';
      row.querySelector('.stock-info').textContent = 'Available Stock: 0';
      row.querySelector('.lot-info').innerHTML = '';

      const lotSelect = row.querySelector('.lot-select');
      lotSelect.innerHTML = '<option value="">-- Select Lot --</option>';
      lotSelect.disabled = true;

      row.querySelector('.total-value').textContent = '$0.00';
      updateSummary();
    }

    function updateLotInfo(select, rowId) {
      const row = document.getElementById(rowId);
      const option = select.selectedOptions[0];

      if (!option.value) {
        row.querySelector('.lot-info').innerHTML = '';
        return;
      }

      const quantity = parseInt(option.dataset.quantity) || 0;
      const purchasePrice = parseFloat(option.dataset.purchasePrice) || 0;
      const amountInLot = quantity * purchasePrice;

      row.querySelector('.lot-info').innerHTML = `
        <div class="lot-amount">Amount in this lot: $${formatCurrency(amountInLot)}</div>
        <div>Available: ${quantity} units | Purchase Price: $${formatCurrency(purchasePrice)}/unit</div>
      `;
    }

    function calculateRowTotal(rowId) {
      const row = document.getElementById(rowId);
      const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
      const price = parseFloat(row.querySelector('.price-input').value) || 0;
      const vatPercent = parseFloat(row.querySelector('.vat-input').value) || 0;

      // Validate quantity doesn't exceed selected lot quantity
      const lotSelect = row.querySelector('.lot-select');
      const selectedOption = lotSelect.selectedOptions[0];
      if (selectedOption && selectedOption.value) {
        const maxQty = parseInt(selectedOption.dataset.quantity) || 0;
        if (qty > maxQty) {
          alert(`Quantity cannot exceed available stock in lot (${maxQty})`);
          row.querySelector('.quantity-input').value = maxQty;
          calculateRowTotal(rowId);
          return;
        }
      }

      const subtotal = qty * price;
      const vatAmount = subtotal * (vatPercent / 100);
      const total = subtotal + vatAmount;

      row.querySelector('.total-value').textContent = '$' + formatCurrency(total);
      updateSummary();
    }

    function addNewRow() {
      const container = document.getElementById('saleItemsContainer');
      const rowId = generateId();
      const rowNumber = rowCounter++;

      // Product options HTML
      let productOptions = '<option value="">-- Select Product --</option>';
      <?php foreach ($products as $p): ?>
        productOptions += `<option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>`;
      <?php endforeach; ?>

      const rowHtml = `
        <div class="sale-item-row" id="${rowId}">
          <div class="row-number">${rowNumber}</div>
          <button type="button" class="btn btn-danger btn-sm remove-row-btn" onclick="removeRow('${rowId}')">
            <i class="bi bi-x"></i>
          </button>
          
          <div class="row g-3 align-items-end">
            <!-- Product Selection -->
            <div class="col-md-3">
              <label class="form-label">Product</label>
              <select class="form-select product-select" onchange="fetchProductLots(this.value, '${rowId}')">
                ${productOptions}
              </select>
            </div>

            <!-- Lot Selection -->
            <div class="col-md-3" style="margin-top: 5px;">
              <label class="form-label">Lot Number</label>
              <select class="form-select lot-select" onchange="updateLotInfo(this, '${rowId}'); calculateRowTotal('${rowId}')" disabled>
                <option value="">-- Select Lot --</option>
              </select>
              <div class="lot-info"></div>
            </div>

            <!-- Quantity -->
            <div class="col-md-2">
              <label class="form-label">Quantity</label>
              <input type="number" class="form-control quantity-input" value="1" min="1" oninput="calculateRowTotal('${rowId}')">
            </div>

            <!-- Selling Price (including VAT) -->
            <div class="col-md-2">
              <label class="form-label">Selling Price</label>
              <input type="number" class="form-control price-input" value="0.00" step="0.01" min="0" oninput="calculateRowTotal('${rowId}')">
            </div>

            <!-- VAT -->
            <div class="col-md-2">
              <label class="form-label">VAT (%)</label>
              <input type="text" class="form-control vat-input" value="0" readonly>
            </div>

            <!-- Total -->
            <div class="col-md-2">
              <label class="form-label">Total</label>
              <div class="total-value">$0.00</div>
            </div>
          </div>

          <!-- Stock Info -->
          <div class="row mt-2">
            <div class="col-12">
              <small class="stock-info">Available Stock: 0</small>
            </div>
          </div>
        </div>
      `;

      container.insertAdjacentHTML('beforeend', rowHtml);

      // Initialize Select2 for product dropdown
      $(`#${rowId} .product-select`).select2({
        placeholder: "-- Select Product --",
        width: '100%'
      });
    }

    function removeRow(rowId) {
      document.getElementById(rowId).remove();
      updateRowNumbers();
      updateSummary();
    }

    function updateRowNumbers() {
      const rows = document.querySelectorAll('.sale-item-row');
      rows.forEach((row, i) => {
        row.querySelector('.row-number').textContent = i + 1;
      });
      rowCounter = rows.length + 1;
    }

    function updateSummary() {
      const rows = document.querySelectorAll('.sale-item-row');
      let total = 0;

      rows.forEach(row => {
        const totalValue = parseFloat(row.querySelector('.total-value').textContent.replace(/,/g, '').replace('$', '')) || 0;
        total += totalValue;
      });

      const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;
      const discountAmount = total * (discountPercent / 100);
      const finalPrice = total - discountAmount;

      document.getElementById('total_price').textContent = '$' + formatCurrency(total);
      document.getElementById('discount_amount').textContent = '$' + formatCurrency(discountAmount);
      document.getElementById('final_price').textContent = '$' + formatCurrency(finalPrice);
    }

    function submitSale() {
      const buyerName = document.getElementById('buyer_name').value.trim();
      const buyerPhone = document.getElementById('buyer_phone').value.trim();
      const saleDate = document.getElementById('sale_date').value;
      const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;

      console.log("=== SALE SUBMISSION DEBUG ===");
      console.log("Buyer Name:", buyerName);
      console.log("Buyer Phone:", buyerPhone);
      console.log("Sale Date:", saleDate);
      console.log("Discount:", discountPercent);

      if (!buyerName) {
        alert('Please enter buyer name');
        return;
      }

      const rows = document.querySelectorAll('.sale-item-row');
      const items = [];

      let isValid = true;
      rows.forEach((row, index) => {
        const productSelect = row.querySelector('.product-select');
        const lotSelect = row.querySelector('.lot-select');
        const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const vat = parseFloat(row.querySelector('.vat-input').value) || 0;

        console.log(`Row ${index}:`, {
          product: productSelect.value,
          lot: lotSelect.value,
          quantity: quantity,
          price: price,
          vat: vat
        });

        if (!productSelect.value || !lotSelect.value || quantity <= 0 || price <= 0) {
          console.error(`Row ${index} has invalid data`);
          isValid = false;
          return;
        }

        items.push({
          product_id: productSelect.value,
          lot: lotSelect.value,
          quantity: quantity,
          selling_price: price,
          vat_percent: vat
        });
      });

      console.log("Items to submit:", items);

      if (!isValid) {
        alert('Please fill all product fields correctly');
        return;
      }

      if (!items.length) {
        alert('Add at least one product');
        return;
      }

      // Submit sale via AJAX
      console.log("Sending AJAX request...");
      fetch('?action=submit_sale', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            buyer_name: buyerName,
            buyer_phone: buyerPhone,
            sale_date: saleDate,
            discount_percent: discountPercent,
            items: items
          })
        })
        .then(res => {
          console.log("Response received:", res);
          return res.json();
        })
        .then(data => {
          console.log("Response data:", data);

          if (data.success) {
            console.log("Sale successful! Redirecting to receipt...");
            window.location.href = `receipt.php?id=${data.receipt_id}`;
          } else {
            console.error("Sale failed with error:", data.error);
            console.error("Debug info:", data.debug);

            // Show detailed error
            if (data.debug) {
              let errorMsg = `Error: ${data.error}\n\n`;
              errorMsg += `Debug Info:\n`;
              if (data.debug.exception_message) {
                errorMsg += `- ${data.debug.exception_message}\n`;
              }
              if (data.debug.mysql_error) {
                errorMsg += `- MySQL: ${data.debug.mysql_error}\n`;
              }
              alert(errorMsg);
            } else {
              alert('Error: ' + data.error);
            }
          }
        })
        .catch(error => {
          console.error('Network/Fetch error:', error);
          alert('Failed to process sale. Check console for details.');
        });
    }

    // Add initial row when page loads
    document.addEventListener('DOMContentLoaded', function() {
      addNewRow();
    });
  </script>
</body>

</html>