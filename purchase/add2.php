<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('add_purchase', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();
$purchasedBy = (int)$_SESSION['user_id'];

/* =========================
   AJAX HANDLERS
========================= */
if (isset($_GET['action'])) {
  header('Content-Type: application/json');

  /* ---- Fetch Products ---- */
  if ($_GET['action'] === 'fetch_products') {
    $supplierId = (int)($_GET['supplier_id'] ?? 0);

    $stmt = $conn->prepare("
      SELECT id, name, cost_price AS purchase_price, vat
      FROM product
      WHERE supplier_id = ? AND status = 'active'
      ORDER BY name ASC
    ");
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'products' => $products]);
    exit;
  }

  /* ---- Submit Purchase ---- */
  if ($_GET['action'] === 'submit_purchase') {
    $data = json_decode(file_get_contents('php://input'), true);
    $supplierId = (int)($data['supplier_id'] ?? 0);
    $items = $data['items'] ?? [];

    if (!$supplierId || empty($items)) {
      echo json_encode(['success' => false, 'error' => 'Invalid data']);
      exit;
    }

    // Generate receipt number
    $today = date('Ymd');
    $receiptNumber = $today . $purchasedBy . substr(md5(uniqid()), 0, 6);

    // Calculate total
    $totalAmount = 0;
    foreach ($items as $i) {
      $qty = (int)$i['quantity'];
      $price = (float)$i['purchase_price'];
      $vatPercent = (float)($i['vat_percent'] ?? 0);
      $subtotal = $qty * $price;
      $totalAmount += $subtotal + ($subtotal * $vatPercent / 100);
    }

    $conn->begin_transaction();

    try {
      /* ---- Insert Receipt ---- */
      $stmtReceipt = $conn->prepare("
        INSERT INTO receipt
        (receipt_number, type, total_amount, created_by, created_at, updated_at)
        VALUES (?, 'purchase', ?, ?, NOW(), NOW())
      ");
      $stmtReceipt->bind_param("sdi", $receiptNumber, $totalAmount, $purchasedBy);
      $stmtReceipt->execute();
      $receiptId = $stmtReceipt->insert_id;
      $stmtReceipt->close();

      /* ---- Insert Purchases ---- */
      $stmtPurchase = $conn->prepare("
        INSERT INTO purchase
        (receipt_id, product_id, supplier_id, quantity, purchase_price, purchase_date, purchased_by, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
      ");

      foreach ($items as $item) {
        $productId = (int)$item['product_id'];
        $quantity  = (int)$item['quantity'];
        $price     = (float)$item['purchase_price'];
        $date      = date('Y-m-d');

        $stmtPurchase->bind_param(
          "iiiidsi",
          $receiptId,
          $productId,
          $supplierId,
          $quantity,
          $price,
          $date,
          $purchasedBy
        );
        $stmtPurchase->execute();

        // Update stock
        $conn->query("
          UPDATE product
          SET quantity_in_stock = quantity_in_stock + $quantity,
              updated_at = NOW()
          WHERE id = $productId
        ");
      }

      $stmtPurchase->close();
      $conn->commit();

      echo json_encode([
        'success' => true,
        'receipt_id' => $receiptId
      ]);
      exit;
    } catch (Exception $e) {
      $conn->rollback();
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
      exit;
    }
  }
}

/* ---- Suppliers ---- */
$suppliers = $conn->query("
  SELECT id, name FROM supplier ORDER BY name ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Purchase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
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

<body>
  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0"><i class="bi bi-cart-plus me-2"></i>Add New Purchase</h2>
          </div>
          <div class="card-body">
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

            <button type="button" class="btn btn-primary" onclick="submitPurchase()"><i class="bi bi-cart-check me-1"></i>Submit Purchase</button>
          </div>
        </div>
      </div>
    </div>
  </div>

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
      const options = productData.map(p => `<option value="${p.id}" data-price="${p.purchase_price}" data-vat="${p.vat}">${p.name}</option>`).join('');
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
      if (!supplierId) return alert('Select a supplier.');

      const rows = document.querySelectorAll('.purchase-item-row');
      const items = [];

      rows.forEach(row => {
        const productSelect = row.querySelector('.product-select');
        if (!productSelect.value) return;

        items.push({
          product_id: productSelect.value,
          quantity: row.querySelector('.quantity-input').value,
          purchase_price: row.querySelector('.price-input').value,
          vat_percent: productSelect.selectedOptions[0].dataset.vat
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
            items
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