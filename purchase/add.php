<?php
// Include auth guard and permissions
include_once __DIR__ . '/../config/auth_guard.php';
requirePermission('add_purchase', '../index.php');

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();
$purchasedBy = (int)$_SESSION['user_id'];

/* ---- Function to generate unique lot ---- */
function generateUniqueLot($conn, $productName, $productId, $purchaseDate)
{
  $maxAttempts = 10;
  $attempt = 0;

  do {
    $attempt++;

    // Include product ID and microtime for better uniqueness
    $microtime = explode(' ', microtime());
    $micro = substr($microtime[0], 2, 6); // Get microseconds

    // Format: YYYYMMDDHHMMSS + microsecond + productId(3) + random(3)
    $datePart = date('YmdHis', strtotime($purchaseDate));
    $lot = strtoupper(
      $datePart .
        $micro .
        str_pad(substr($productId, -3), 3, '0', STR_PAD_LEFT) .
        str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT)
    );

    // Truncate to reasonable length if needed
    $lot = substr($lot, 0, 20);

    // Check if lot already exists
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM purchase WHERE lot = ?");
    $stmt->bind_param("s", $lot);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_object();
    $exists = ($result->cnt > 0);
    $stmt->close();

    // If we've tried too many times, use a different approach
    if ($attempt >= $maxAttempts && $exists) {
      // Fallback: Use UUID-like approach
      $lot = strtoupper(
        'LOT' .
          date('Ymd') .
          $productId .
          bin2hex(random_bytes(3))
      );

      // Check fallback lot also
      $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM purchase WHERE lot = ?");
      $stmt->bind_param("s", $lot);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_object();
      $exists = ($result->cnt > 0);
      $stmt->close();

      if (!$exists) {
        return $lot;
      }
    }
  } while ($exists && $attempt < $maxAttempts);

  return $lot;
}

/* ---- AJAX HANDLERS ---- */
if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  /* Fetch Products */
  if ($_GET['action'] === 'fetch_products') {
    $supplierId = (int)($_GET['supplier_id'] ?? 0);
    $stmt = $conn->prepare("
            SELECT id, name, cost_price AS purchase_price, vat, quantity_in_stock
            FROM product
            WHERE supplier_id = ? AND status = 'active'
            ORDER BY name ASC
        ");
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'products' => $products]);
    exit;
  }

  /* Submit Purchase - COMPLETE UPDATED VERSION */
  if ($_GET['action'] === 'submit_purchase') {
    $data = json_decode(file_get_contents('php://input'), true);
    $supplierId = (int)($data['supplier_id'] ?? 0);
    $items = $data['items'] ?? [];
    $discountPercent = floatval($data['discount_percent'] ?? 0);
    $purchaseDate = $data['purchase_date'] ?? date('Y-m-d');

    if (!$supplierId || empty($items)) {
      echo json_encode(['success' => false, 'error' => 'Invalid data - supplier or items missing']);
      exit;
    }

    // Validate all items have required data
    foreach ($items as $i) {
      if (empty($i['product_id']) || empty($i['quantity']) || empty($i['purchase_price'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid item data']);
        exit;
      }
    }

    // Generate receipt number
    $today = date('Ymd');
    $receiptNumber = $today . $purchasedBy . substr(md5(uniqid()), 0, 6);

    // Calculate total including VAT
    $totalAmount = 0;
    foreach ($items as $i) {
      $qty = (int)$i['quantity'];
      $price = (float)$i['purchase_price'];
      $vatPercent = (float)($i['vat_percent'] ?? 0);
      $subtotal = $qty * $price;
      $totalAmount += $subtotal + ($subtotal * $vatPercent / 100);
    }

    $discountValue = $totalAmount * ($discountPercent / 100);
    $finalAmount = $totalAmount - $discountValue;

    $conn->begin_transaction();
    try {
      // Insert receipt
      $stmtReceipt = $conn->prepare("
                INSERT INTO receipt
                (receipt_number, type, total_amount, discount_value, created_by, created_at, updated_at)
                VALUES (?, 'purchase', ?, ?, ?, NOW(), NOW())
            ");
      $stmtReceipt->bind_param("sdii", $receiptNumber, $totalAmount, $discountValue, $purchasedBy);
      if (!$stmtReceipt->execute()) {
        throw new Exception("Failed to create receipt: " . $conn->error);
      }
      $receiptId = $stmtReceipt->insert_id;
      $stmtReceipt->close();

      // Prepare purchase insert statement
      $stmtPurchase = $conn->prepare("
                INSERT INTO purchase
                (receipt_id, product_id, supplier_id, quantity, product_left, purchase_price, lot, purchase_date, purchased_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

      // Prepare product update statement
      $stmtUpdateProduct = $conn->prepare("
                UPDATE product
                SET quantity_in_stock = quantity_in_stock + ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

      $itemIndex = 0;
      foreach ($items as $item) {
        $itemIndex++;
        $productId = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        $price = (float)$item['purchase_price'];

        // Current stock
        $currentStock = $quantity;

        // Get product name for lot generation
        $stmtProduct = $conn->prepare("SELECT name FROM product WHERE id = ?");
        $stmtProduct->bind_param("i", $productId);
        $stmtProduct->execute();
        $productResult = $stmtProduct->get_result();
        if ($productResult->num_rows === 0) {
          throw new Exception("Product ID $productId not found");
        }
        $productName = $productResult->fetch_object()->name ?? '';
        $stmtProduct->close();

        // Generate unique lot with improved function
        $lot = generateUniqueLot($conn, $productName, $productId, $purchaseDate);

        // Insert purchase record
        $stmtPurchase->bind_param(
          "iiiidsiss",
          $receiptId,
          $productId,
          $supplierId,
          $quantity,
          $currentStock,
          $price,
          $lot,
          $purchaseDate,
          $purchasedBy
        );

        if (!$stmtPurchase->execute()) {
          // Check if it's a duplicate lot error
          if (strpos($conn->error, 'unique_lot') !== false || strpos($conn->error, 'Duplicate entry') !== false) {
            // Try one more time with a different lot
            $lot = 'LOT-' . uniqid() . '-' . $productId . '-' . time();
            $stmtPurchase->bind_param(
              "iiiidsiss",
              $receiptId,
              $productId,
              $supplierId,
              $quantity,
              $currentStock,
              $price,
              $lot,
              $purchaseDate,
              $purchasedBy
            );

            if (!$stmtPurchase->execute()) {
              throw new Exception("Failed to insert purchase for product ID $productId: " . $conn->error);
            }
          } else {
            throw new Exception("Failed to insert purchase for product ID $productId: " . $conn->error);
          }
        }

        // Update product stock
        $stmtUpdateProduct->bind_param("ii", $quantity, $productId);
        if (!$stmtUpdateProduct->execute()) {
          throw new Exception("Failed to update stock for product ID $productId: " . $conn->error);
        }
      }

      $stmtPurchase->close();
      $stmtUpdateProduct->close();

      // Commit transaction
      $conn->commit();

      echo json_encode([
        'success' => true,
        'receipt_id' => $receiptId,
        'message' => 'Purchase completed successfully'
      ]);
      exit;
    } catch (Exception $e) {
      // Rollback transaction on error
      $conn->rollback();

      // Log error for debugging
      error_log("Purchase Error: " . $e->getMessage());

      echo json_encode([
        'success' => false,
        'error' => 'Failed to process purchase: ' . $e->getMessage()
      ]);
      exit;
    }
  }
}

/* ---- Suppliers ---- */
$suppliers = $conn->query("SELECT id, name FROM supplier ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Purchase | Sass Inventory Management System</title>
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
    .purchase-item-row {
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
          <h3 class="mb-0" style="font-weight: 800;">Add New Purchase</h3>
        </div>
      </div>

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>


      <!-- App Main -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <div class="card card-custom shadow-sm">
            <div class="card-body">
              <!-- Header -->
              <h4 class=" fw-bold text-secondary border-bottom pb-2">
                Add Purchase Information
              </h4>

              <!-- Purchase Form -->
              <div class="card-body">
                <!-- Alert -->
                <div class="alert alert-info">
                  <i class="bi bi-info-circle"></i> Once a purchase is created, it cannot be edited.
                </div>

                <!-- Supplier & Purchase Date -->
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="supplier_id" class="form-label"><i class="bi bi-truck me-1"></i>Select Supplier</label>
                    <select id="supplier_id" class="form-select">
                      <option value="">-- Choose a Supplier --</option>
                      <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label for="purchase_date" class="form-label"><i class="bi bi-calendar-date me-1"></i>Purchase Date</label>
                    <input type="date" id="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
                  </div>
                </div>

                <!-- Purchase Items -->
                <div id="purchaseItemsContainer" class="products-container"></div>

                <div class="mt-3">
                  <button type="button" class="btn btn-success" onclick="addNewRow()"><i class="bi bi-plus-circle me-1"></i>Add Product</button>
                </div>

                <div class="row mt-4 align-items-center">
                  <div class="col-md-6">
                    <label for="discount_percent" class="form-label"><i class="bi bi-percent me-1"></i>Discount (%)</label>
                    <input type="number" id="discount_percent" class="form-control" value="0" min="0" max="100" step="0.01" oninput="updateSummary()">
                  </div>
                  <div class="col-md-6">
                    <div class="p-3">
                      <div class="d-flex justify-content-between mb-2">
                        <span>Total Price:</span>
                        <span id="total_price">$0.00</span>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                        <span>Discount:</span>
                        <span id="discount_amount">$0.00</span>
                      </div>
                      <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Final Price:</span>
                        <span id="final_price">$0.00</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Save & Cancel Buttons -->
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-primary" onclick="submitPurchase()">
                    <i class="bi bi-cart-check me-1"></i> Save Purchase
                  </button>
                  <a href="index.php" class="btn btn-secondary px-4 py-2">
                    <i class="bi bi-x-circle"></i> Cancel
                  </a>
                </div>
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
    let productData = [];
    let rowCounter = 1;

    // Fetch products when supplier changes
    document.getElementById('supplier_id').addEventListener('change', function() {
      const supplierId = this.value;
      if (!supplierId) return;

      fetch(`?action=fetch_products&supplier_id=${supplierId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            productData = data.products;
            rowCounter = 1;
            document.getElementById('purchaseItemsContainer').innerHTML = '';
            addNewRow();
          } else {
            alert('No products found for this supplier.');
          }
        });
    });

    function formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(amount);
    }


    function generateId() {
      return 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    }

    function addNewRow() {
      if (!productData.length) return alert('Select a supplier first.');
      const container = document.getElementById('purchaseItemsContainer');
      const rowId = generateId();
      const rowNumber = rowCounter++;
      const options = productData.map(p =>
        `<option value="${p.id}" 
           data-price="${p.purchase_price}" 
           data-vat="${p.vat}" 
           data-stock="${p.quantity_in_stock}">
     ${p.name}
  </option>`).join('');
      const rowHtml = `
    <div class="purchase-item-row" id="${rowId}">
        <div class="row-number">${rowNumber}</div>
        <button type="button" class="btn btn-danger btn-sm remove-row-btn" onclick="removeRow('${rowId}')"><i class="bi bi-x"></i></button>
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select class="form-select product-select" onchange="updatePriceAndVat(this,'${rowId}')">
                    <option value="">-- Select Product --</option>${options}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control quantity-input" value="1" min="1" oninput="calculateRowTotal('${rowId}')">
            </div>
            <div class="col-md-2">
                <label class="form-label">Price</label>
                <input type="number" class="form-control price-input" value="0" step="0.01" min="0" oninput="calculateRowTotal('${rowId}')">
            </div>
            <div class="col-md-2">
                <label class="form-label vat-label">VAT</label>
                <input type="text" class="form-control vat-input" value="0" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Total</label>
                <div class="total-value">$0.00</div>
            </div>
        </div>
    </div>`;
      container.insertAdjacentHTML('beforeend', rowHtml);
    }

    function removeRow(rowId) {
      document.getElementById(rowId).remove();
      updateRowNumbers();
    }

    function updateRowNumbers() {
      const rows = document.querySelectorAll('.purchase-item-row');
      rows.forEach((row, i) => row.querySelector('.row-number').textContent = i + 1);
    }

    function updatePriceAndVat(select, rowId) {
      const row = document.getElementById(rowId);
      const opt = select.selectedOptions[0];
      if (!opt.value) return;

      // Update price input
      row.querySelector('.price-input').value = parseFloat(opt.dataset.price).toFixed(2);

      // Update VAT label with percentage
      const vatLabel = row.querySelector('.vat-label');
      vatLabel.textContent = `VAT (${parseFloat(opt.dataset.vat)}%)`;

      // Recalculate totals
      calculateRowTotal(rowId);
    }


    function calculateRowTotal(rowId) {
      const row = document.getElementById(rowId);
      const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
      const price = parseFloat(row.querySelector('.price-input').value) || 0;
      const vatPercent = parseFloat(row.querySelector('.product-select').selectedOptions[0]?.dataset.vat) || 0;
      const subtotal = qty * price;
      const vatAmount = subtotal * (vatPercent / 100);
      const total = subtotal + vatAmount;

      // Format the uneditable fields
      row.querySelector('.vat-input').value = formatCurrency(vatAmount);
      row.querySelector('.total-value').textContent = '$' + formatCurrency(total);

      updateSummary();
    }


    function submitPurchase() {
      const supplierId = document.getElementById('supplier_id').value;
      const purchaseDate = document.getElementById('purchase_date').value;
      const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;

      if (!supplierId) return alert('Select a supplier.');
      if (!purchaseDate) return alert('Select a purchase date.');

      const rows = document.querySelectorAll('.purchase-item-row');
      const items = [];

      rows.forEach(row => {
        const productSelect = row.querySelector('.product-select');
        if (!productSelect.value) return;

        const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const vat = parseFloat(productSelect.selectedOptions[0]?.dataset.vat) || 0;
        const stock = parseInt(productSelect.selectedOptions[0]?.dataset.stock) || 0;

        items.push({
          product_id: productSelect.value,
          quantity: qty,
          purchase_price: price,
          vat_percent: vat
        });

      });

      if (!items.length) return alert('Add at least one product.');

      fetch('?action=submit_purchase', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            supplier_id: supplierId,
            purchase_date: purchaseDate,
            discount_percent: discountPercent,
            items: items
          })
        })
        .then(res => res.json())
        .then(resp => {
          if (resp.success) {
            window.location.href = `receipt.php?id=${resp.receipt_id}`;
          } else {
            alert('Error: ' + resp.error);
          }
        });
    }



    function updateSummary() {
      const rows = document.querySelectorAll('.purchase-item-row');
      let total = 0;
      rows.forEach(row => {
        const totalValue = parseFloat(row.querySelector('.total-value').textContent.replace(/,/g, '').replace('$', '')) || 0;
        total += totalValue;
      });

      const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;
      const discountAmount = total * (discountPercent / 100);
      const finalPrice = total - discountAmount;

      // Format static display values
      document.getElementById('total_price').textContent = '$' + formatCurrency(total);
      document.getElementById('discount_amount').textContent = '$' + formatCurrency(discountAmount);
      document.getElementById('final_price').textContent = '$' + formatCurrency(finalPrice);
    }
  </script>
</body>

</html>