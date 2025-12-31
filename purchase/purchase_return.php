<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('product_return', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$conn = connectDB();

// Get lot_number from query string safely
$lot_number = isset($_GET['lot']) ? $_GET['lot'] : '';
if (!$lot_number) {
  die("Lot number is required.");
}

// Get purchase with receipt and related information
$purchaseSql = "SELECT p.*, 
                pr.name as product_name,
                pr.sku as product_sku,
                s.name as supplier_name,
                s.phone as supplier_phone,
                u.username as purchased_by_name,
                r.receipt_number,
                r.total_amount as receipt_total,
                r.discount_value as receipt_discount,
                r.created_at as receipt_date
                FROM purchase p
                LEFT JOIN product pr ON p.product_id = pr.id
                LEFT JOIN supplier s ON p.supplier_id = s.id
                LEFT JOIN user u ON p.purchased_by = u.id
                LEFT JOIN receipt r ON p.receipt_id = r.id
                WHERE p.lot = ?";
$stmt = $conn->prepare($purchaseSql);
$stmt->bind_param("s", $lot_number);
$stmt->execute();
$result = $stmt->get_result();
$purchase = $result->fetch_assoc();

if (!$purchase) {
  die("Purchase not found for the provided lot number.");
}

// Get all items in this receipt to show context
$receiptItemsSql = "SELECT p.*, pr.name as product_name 
                    FROM purchase p
                    LEFT JOIN product pr ON p.product_id = pr.id
                    WHERE p.receipt_id = ?";
$receiptStmt = $conn->prepare($receiptItemsSql);
$receiptStmt->bind_param("i", $purchase['receipt_id']);
$receiptStmt->execute();
$receiptItemsResult = $receiptStmt->get_result();

// Get current product quantity in stock for reference
$productSql = "SELECT quantity_in_stock, selling_price FROM product WHERE id = ?";
$productStmt = $conn->prepare($productSql);
$productStmt->bind_param("i", $purchase['product_id']);
$productStmt->execute();
$productResult = $productStmt->get_result();
$productData = $productResult->fetch_assoc();

// Calculate maximum returnable quantity
$maxReturnable = min($purchase['product_left'], $productData['quantity_in_stock']);

// Calculate receipt summary
$receiptTotal = $purchase['receipt_total'] ?? 0;
$receiptDiscount = $purchase['receipt_discount'] ?? 0;
$netAmount = $receiptTotal - $receiptDiscount;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Return Purchase | Sass Inventory Management System</title>
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
          <h3 class="mb-0" style="font-weight: 800;">Return Purchase</h3>
          <?php if (!empty($purchase['receipt_number'])): ?>
            <div class="d-flex align-items-center">
              <span class="badge bg-info me-2">Receipt: <?= htmlspecialchars($purchase['receipt_number']) ?></span>
              <a href="receipt.php?id=<?= $purchase['receipt_id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-receipt"></i> View Full Receipt
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Form Error -->
      <?php if (!empty($formError)): ?>
        <div id="errorBox" class="alert alert-danger text-center">
          <?= htmlspecialchars($formError) ?>
        </div>
      <?php endif; ?>

      <!-- Container -->
      <div class="container py-4">

        <!-- Receipt Summary Card -->
        <?php if (!empty($purchase['receipt_number'])): ?>
          <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="bi bi-receipt"></i> Receipt Summary</h5>
              <span class="badge bg-light text-primary">#<?= htmlspecialchars($purchase['receipt_number']) ?></span>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <table class="table table-borderless">
                    <tr>
                      <th width="40%">Receipt Number:</th>
                      <td><strong>#<?= htmlspecialchars($purchase['receipt_number']) ?></strong></td>
                    </tr>
                    <tr>
                      <th>Receipt Date:</th>
                      <td><?= !empty($purchase['receipt_date']) ? date('d M Y h:i A', strtotime($purchase['receipt_date'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                      <th>Total Amount:</th>
                      <td class="fw-bold">$<?= htmlspecialchars(number_format($receiptTotal, 2)) ?></td>
                    </tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <table class="table table-borderless">
                    <tr>
                      <th width="40%">Discount:</th>
                      <td class="text-danger">-$<?= htmlspecialchars(number_format($receiptDiscount, 2)) ?></td>
                    </tr>
                    <tr>
                      <th>Net Amount:</th>
                      <td class="fw-bold text-success">$<?= htmlspecialchars(number_format($netAmount, 2)) ?></td>
                    </tr>
                    <tr>
                      <th>Items in Receipt:</th>
                      <td>
                        <span class="badge bg-info"><?= $receiptItemsResult->num_rows ?> items</span>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>

              <!-- Receipt Items Table -->
              <div class="mt-4">
                <h6 class="border-bottom pb-2">Items in This Receipt</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Available</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $counter = 1;
                      while ($item = $receiptItemsResult->fetch_assoc()):
                        $isCurrentItem = $item['lot'] == $lot_number;
                      ?>
                        <tr class="<?= $isCurrentItem ? 'table-warning fw-bold' : '' ?>">
                          <td><?= $counter++ ?></td>
                          <td>
                            <?= htmlspecialchars($item['product_name']) ?>
                            <?php if ($isCurrentItem): ?>
                              <span class="badge bg-warning text-dark ms-2">RETURNING</span>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($item['quantity']) ?></td>
                          <td>$<?= htmlspecialchars(number_format($item['purchase_price'], 2)) ?></td>
                          <td>$<?= htmlspecialchars(number_format($item['quantity'] * $item['purchase_price'], 2)) ?></td>
                          <td>
                            <span class="badge <?= $item['product_left'] > 0 ? 'bg-success' : 'bg-secondary' ?>">
                              <?= htmlspecialchars($item['product_left']) ?> left
                            </span>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Purchase Information Card -->
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Purchase Information</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Product Name:</th>
                    <td>
                      <?= htmlspecialchars($purchase['product_name'] ?? 'N/A') ?>
                      <?php if (!empty($purchase['product_sku'])): ?>
                        <br><small class="text-muted">SKU: <?= htmlspecialchars($purchase['product_sku']) ?></small>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th>Supplier:</th>
                    <td>
                      <?= htmlspecialchars($purchase['supplier_name'] ?? 'N/A') ?>
                      <?php if (!empty($purchase['supplier_phone'])): ?>
                        <br><small class="text-muted">Tel: <?= htmlspecialchars($purchase['supplier_phone']) ?></small>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <th>Purchase Date:</th>
                    <td><?= !empty($purchase['purchase_date']) ? date('d M Y', strtotime($purchase['purchase_date'])) : 'N/A' ?></td>
                  </tr>
                  <tr>
                    <th>Purchased By:</th>
                    <td><?= htmlspecialchars($purchase['purchased_by_name'] ?? 'N/A') ?></td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Lot Number:</th>
                    <td><span class="badge bg-dark"><?= htmlspecialchars($lot_number) ?></span></td>
                  </tr>
                  <tr>
                    <th>Original Quantity:</th>
                    <td>
                      <span class="badge bg-primary"><?= htmlspecialchars($purchase['quantity']) ?> units</span>
                      <br><small>Total: $<?= htmlspecialchars(number_format($purchase['quantity'] * $purchase['purchase_price'], 2)) ?></small>
                    </td>
                  </tr>
                  <tr>
                    <th>Available for Return:</th>
                    <td>
                      <span class="badge <?= $purchase['product_left'] > 0 ? 'bg-success' : 'bg-danger' ?> fs-6">
                        <?= htmlspecialchars($purchase['product_left']) ?> units
                      </span>
                      <br><small>Max refund: $<?= htmlspecialchars(number_format($purchase['product_left'] * $purchase['purchase_price'], 2)) ?></small>
                    </td>
                  </tr>
                  <tr>
                    <th>Current Selling Price:</th>
                    <td>
                      <span class="text-success">$<?= htmlspecialchars(number_format($productData['selling_price'] ?? 0, 2)) ?></span>
                      <br><small>Purchase: $<?= htmlspecialchars(number_format($purchase['purchase_price'], 2)) ?></small>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Return Form Card -->
        <div class="card">
          <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="bi bi-arrow-return-left"></i> Return Products</h5>
          </div>
          <div class="card-body">
            <form id="returnForm" action="process_return.php" method="POST">
              <input type="hidden" name="lot_number" value="<?= htmlspecialchars($lot_number) ?>">
              <input type="hidden" name="product_id" value="<?= htmlspecialchars($purchase['product_id']) ?>">
              <input type="hidden" name="purchase_id" value="<?= htmlspecialchars($purchase['id']) ?>">
              <input type="hidden" name="receipt_id" value="<?= htmlspecialchars($purchase['receipt_id'] ?? '') ?>">
              <input type="hidden" name="receipt_number" value="<?= htmlspecialchars($purchase['receipt_number'] ?? '') ?>">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="return_quantity" class="form-label">
                    <strong>Quantity to Return *</strong>
                    <small class="text-muted">(Maximum: <?= $maxReturnable ?> units)</small>
                  </label>
                  <div class="input-group">
                    <input type="number"
                      class="form-control"
                      id="return_quantity"
                      name="return_quantity"
                      min="1"
                      max="<?= $maxReturnable ?>"
                      value="1"
                      required
                      onchange="calculateRefund()">
                    <span class="input-group-text">units</span>
                  </div>
                  <div class="form-text">
                    <i class="bi bi-info-circle"></i>
                    Available: <?= $purchase['product_left'] ?> units |
                    Current stock: <?= $productData['quantity_in_stock'] ?? 0 ?> units
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="refund_amount" class="form-label"><strong>Refund Amount</strong></label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="text"
                      class="form-control"
                      id="refund_amount"
                      name="refund_amount"
                      readonly
                      value="<?= number_format($purchase['purchase_price'], 2) ?>">
                  </div>
                  <div class="row mt-1">
                    <div class="col-6">
                      <small class="text-muted">Unit: $<?= number_format($purchase['purchase_price'], 2) ?></small>
                    </div>
                    <div class="col-6 text-end">
                      <small id="totalRefundText" class="text-success"></small>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="return_reason" class="form-label"><strong>Reason for Return *</strong></label>
                  <select class="form-select" id="return_reason" name="return_reason" required>
                    <option value="" selected disabled>Select a reason</option>
                    <option value="defective">Defective Product</option>
                    <option value="wrong_item">Wrong Item Received</option>
                    <option value="damaged">Damaged in Transit</option>
                    <option value="quality_issue">Quality Issues</option>
                    <option value="excess_stock">Excess Stock</option>
                    <option value="expired">Expired Product</option>
                    <option value="customer_return">Customer Returned Item</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="return_date" class="form-label"><strong>Return Date *</strong></label>
                  <input type="date"
                    class="form-control"
                    id="return_date"
                    name="return_date"
                    value="<?= date('Y-m-d') ?>"
                    max="<?= date('Y-m-d') ?>"
                    required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="refund_method" class="form-label"><strong>Refund Method</strong></label>
                  <select class="form-select" id="refund_method" name="refund_method">
                    <option value="cash" selected>Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_note">Credit Note</option>
                    <option value="adjustment">Stock Adjustment</option>
                    <option value="exchange">Exchange for Other Product</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="condition" class="form-label"><strong>Returned Item Condition</strong></label>
                  <select class="form-select" id="condition" name="condition">
                    <option value="new" selected>New/Unopened</option>
                    <option value="opened">Opened but Unused</option>
                    <option value="used">Used</option>
                    <option value="damaged">Damaged</option>
                    <option value="defective">Defective</option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label for="notes" class="form-label"><strong>Additional Notes</strong></label>
                <textarea class="form-control"
                  id="notes"
                  name="notes"
                  rows="3"
                  placeholder="Any additional information about this return (e.g., serial numbers, specific issues, etc.)..."></textarea>
              </div>

              <div class="alert alert-info">
                <i class="bi bi-exclamation-circle"></i>
                <strong>Important:</strong> This return will:
                <ul class="mb-0 mt-1">
                  <li>Reduce stock by the returned quantity</li>
                  <li>Update available quantity for this purchase lot</li>
                  <li>Create a return record for tracking</li>
                  <li>Optionally issue a refund to the supplier</li>
                </ul>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <div>
                  <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Purchases
                  </a>
                  <?php if (!empty($purchase['receipt_number'])): ?>
                    <a href="receipt.php?id=<?= $purchase['receipt_id'] ?>" class="btn btn-outline-primary ms-2">
                      <i class="bi bi-receipt"></i> View Receipt
                    </a>
                  <?php endif; ?>
                </div>
                <div>
                  <button type="button" class="btn btn-outline-danger me-2" onclick="previewReturn()">
                    <i class="bi bi-eye"></i> Preview
                  </button>
                  <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-circle"></i> Process Return
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>

  </div>

  <!-- Preview Modal -->
  <div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title"><i class="bi bi-eye"></i> Return Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="previewContent">
          <!-- Preview content will be inserted here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" onclick="submitForm()">Confirm & Process Return</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

  <script>
    // Calculate refund amount based on quantity
    function calculateRefund() {
      const unitPrice = <?= $purchase['purchase_price'] ?>;
      const quantity = document.getElementById('return_quantity').value;
      const refundAmount = unitPrice * quantity;

      document.getElementById('refund_amount').value = refundAmount.toFixed(2);
      document.getElementById('totalRefundText').textContent = 'Total: $' + refundAmount.toFixed(2);
    }

    // Preview return before submission
    function previewReturn() {
      const quantity = document.getElementById('return_quantity').value;
      const reason = document.getElementById('return_reason').value;
      const reasonText = document.getElementById('return_reason').options[document.getElementById('return_reason').selectedIndex].text;
      const returnDate = document.getElementById('return_date').value;
      const refundMethod = document.getElementById('refund_method').value;
      const condition = document.getElementById('condition').value;
      const notes = document.getElementById('notes').value;
      const refundAmount = document.getElementById('refund_amount').value;

      if (!quantity || quantity < 1 || quantity > <?= $maxReturnable ?>) {
        alert(`Please enter a valid quantity between 1 and <?= $maxReturnable ?>.`);
        return;
      }

      if (!reason) {
        alert('Please select a reason for return.');
        return;
      }

      const previewContent = `
        <div class="row">
          <div class="col-md-6">
            <table class="table table-bordered">
              <tr><th>Product:</th><td><?= htmlspecialchars($purchase['product_name']) ?></td></tr>
              <tr><th>Lot Number:</th><td><?= htmlspecialchars($lot_number) ?></td></tr>
              <tr><th>Quantity to Return:</th><td>${quantity} units</td></tr>
              <tr><th>Refund Amount:</th><td class="text-success fw-bold">$${refundAmount}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-bordered">
              <tr><th>Return Reason:</th><td>${reasonText}</td></tr>
              <tr><th>Return Date:</th><td>${returnDate}</td></tr>
              <tr><th>Refund Method:</th><td>${refundMethod}</td></tr>
              <tr><th>Item Condition:</th><td>${condition}</td></tr>
            </table>
          </div>
        </div>
        ${notes ? `<div class="alert alert-info mt-3"><strong>Notes:</strong><br>${notes}</div>` : ''}
        <div class="alert alert-warning mt-3">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Please review carefully:</strong> Once processed, this return cannot be undone automatically.
        </div>
      `;

      document.getElementById('previewContent').innerHTML = previewContent;
      new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    // Submit form after preview confirmation
    function submitForm() {
      document.getElementById('returnForm').submit();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      calculateRefund();

      // Validate max quantity
      const quantityInput = document.getElementById('return_quantity');
      const maxQuantity = <?= $maxReturnable ?>;

      quantityInput.addEventListener('change', function() {
        if (this.value > maxQuantity) {
          alert(`Cannot return more than ${maxQuantity} units.`);
          this.value = maxQuantity;
          calculateRefund();
        }
        if (this.value < 1) {
          this.value = 1;
          calculateRefund();
        }
      });

      // Form validation
      document.getElementById('returnForm').addEventListener('submit', function(e) {
        const quantity = parseInt(document.getElementById('return_quantity').value);
        const reason = document.getElementById('return_reason').value;

        if (quantity < 1 || quantity > maxQuantity) {
          e.preventDefault();
          alert(`Please enter a valid quantity between 1 and ${maxQuantity}.`);
          return false;
        }

        if (!reason) {
          e.preventDefault();
          alert('Please select a reason for return.');
          return false;
        }

        return confirm('Are you sure you want to process this return?');
      });
    });
  </script>

</body>

</html>