<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_all_receipts', '../index.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All My Receipts | Sass Inventory Management System</title>
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

  <!-- DataTables (Needed for user list table) -->
  <link rel="stylesheet"
    href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

  <!-- Custom CSS -->
  <style>
    .btn-warning:hover {
      background-color: #d39e00 !important;
      border-color: #c99700 !important;
    }

    .btn-danger:hover {
      background-color: #bb2d3b !important;
      border-color: #b02a37 !important;
    }

    .badge {
      font-size: 0.75em;
      padding: 0.3em 0.6em;
    }

    .table-sm th,
    .table-sm td {
      padding: 0.5rem;
    }

    .receipt-type {
      font-size: 0.8em;
      padding: 0.2em 0.5em;
    }

    .type-purchase {
      background-color: #0dcaf0 !important;
      color: #000 !important;
    }

    .type-sale {
      background-color: #198754 !important;
      color: #fff !important;
    }

    .amount-cell {
      font-weight: 600;
    }

    .btn-group-sm .btn {
      padding: 0.25rem 0.5rem;
    }

    .customer-info {
      max-width: 120px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .stock-badge {
      font-size: 0.7em;
    }

    .stock-good {
      background-color: #d1e7dd !important;
      color: #0f5132 !important;
    }

    .stock-medium {
      background-color: #fff3cd !important;
      color: #664d03 !important;
    }

    .stock-low {
      background-color: #f8d7da !important;
      color: #842029 !important;
    }

    .stat-card {
      transition: transform 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
      font-size: 2.5rem;
      opacity: 0.8;
    }
  </style>
</head>

<?php
$conn = connectDB();
$userId = $_SESSION['user_id'];

// Fetch all my receipts with enhanced information
$sql = "
    SELECT 
        r.*,
        -- Purchase information
        (SELECT COUNT(*) FROM purchase p WHERE p.receipt_id = r.id) AS purchase_items,
        (SELECT SUM(p.quantity) FROM purchase p WHERE p.receipt_id = r.id) AS purchase_quantity,
        (SELECT SUM(p.product_left) FROM purchase p WHERE p.receipt_id = r.id) AS purchase_stock_left,
        (SELECT s.name FROM purchase p 
         LEFT JOIN supplier s ON p.supplier_id = s.id 
         WHERE p.receipt_id = r.id LIMIT 1) AS supplier_name,
        -- Sale information
        (SELECT COUNT(*) FROM sale s WHERE s.receipt_id = r.id) AS sale_items,
        (SELECT SUM(s.quantity) FROM sale s WHERE s.receipt_id = r.id) AS sale_quantity,
        (SELECT MAX(s.buyer_name) FROM sale s WHERE s.receipt_id = r.id) AS buyer_name,
        (SELECT MAX(s.buyer_phone) FROM sale s WHERE s.receipt_id = r.id) AS buyer_phone
    FROM receipt r
    WHERE r.created_by = ?
    ORDER BY r.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Get my total statistics
$statsSql = "
    SELECT 
        COUNT(*) as total_receipts,
        SUM(r.total_amount) as total_amount,
        SUM(r.discount_value) as total_discount,
        SUM(CASE WHEN r.type = 'purchase' THEN 1 ELSE 0 END) as purchase_receipts,
        SUM(CASE WHEN r.type = 'sale' THEN 1 ELSE 0 END) as sale_receipts,
        SUM(CASE WHEN r.type = 'purchase' THEN r.total_amount ELSE 0 END) as purchase_amount,
        SUM(CASE WHEN r.type = 'sale' THEN r.total_amount ELSE 0 END) as sale_amount
    FROM receipt r
    WHERE r.created_by = ?
";
$statsStmt = $conn->prepare($statsSql);
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

// Calculate purchase stock statistics
$stockSql = "
    SELECT 
        SUM(p.quantity) as total_purchase_qty,
        SUM(p.product_left) as total_stock_left
    FROM purchase p
    INNER JOIN receipt r ON p.receipt_id = r.id
    WHERE r.created_by = ?
";
$stockStmt = $conn->prepare($stockSql);
$stockStmt->bind_param("i", $userId);
$stockStmt->execute();
$stockResult = $stockStmt->get_result()->fetch_assoc();
$stockStmt->close();

$totalPurchaseQty = $stockResult['total_purchase_qty'] ?? 0;
$totalStockLeft = $stockResult['total_stock_left'] ?? 0;
$stockPercentage = $totalPurchaseQty > 0 ? ($totalStockLeft / $totalPurchaseQty) * 100 : 0;
?>

<!-- Body -->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!--Header-->
    <?php include_once '../Inc/Navbar.php'; ?>

    <!--Sidebar-->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <!-- Main -->
    <main class="app-main">
      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <!-- Page Title -->
          <h3 class="mb-0" style="font-weight: 800;">All My Receipts</h3>
        </div>
      </div>

      <!-- Success/Fail Messages -->
      <?php if (!empty($_SESSION['success_message'])): ?>
        <div id="successMsg" class="alert alert-success mt-3"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['fail_message'])): ?>
        <div id="failMsg" class="alert alert-danger mt-3"><?= $_SESSION['fail_message'] ?></div>
        <?php unset($_SESSION['fail_message']); ?>
      <?php endif; ?>

      <!-- Summary Cards -->
      <div class="container-fluid mt-3">
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Receipts</h6>
                    <h3 class="mb-0"><?= $statsResult['total_receipts'] ?? 0 ?></h3>
                    <small class="opacity-75">My all receipts</small>
                  </div>
                  <i class="bi bi-receipt stat-icon"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Purchases</h6>
                    <h3 class="mb-0"><?= $statsResult['purchase_receipts'] ?? 0 ?></h3>
                    <small class="opacity-75">$<?= number_format($statsResult['purchase_amount'] ?? 0, 2) ?></small>
                  </div>
                  <i class="bi bi-cart-plus stat-icon"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Sales</h6>
                    <h3 class="mb-0"><?= $statsResult['sale_receipts'] ?? 0 ?></h3>
                    <small class="opacity-75">$<?= number_format($statsResult['sale_amount'] ?? 0, 2) ?></small>
                  </div>
                  <i class="bi bi-cart-check stat-icon"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card stat-card bg-warning text-dark">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Stock Left</h6>
                    <h3 class="mb-0"><?= $totalStockLeft ?></h3>
                    <small class="opacity-75"><?= number_format($stockPercentage, 1) ?>% of <?= $totalPurchaseQty ?></small>
                  </div>
                  <i class="bi bi-inboxes stat-icon"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="app-content-body">
        <div class="container-fluid">
          <?php if ($result->num_rows > 0): ?>
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">My Receipts History</h5>
                <div class="text-muted small mt-1">
                  My Total: <?= $statsResult['total_receipts'] ?? 0 ?> receipts |
                  Purchase: <?= $statsResult['purchase_receipts'] ?? 0 ?> |
                  Sale: <?= $statsResult['sale_receipts'] ?? 0 ?> |
                  Amount: $<?= number_format($statsResult['total_amount'] ?? 0, 2) ?> |
                  Discount: $<?= number_format($statsResult['total_discount'] ?? 0, 2) ?>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="myAllReceiptsTable" class="table table-bordered table-striped table-hover align-middle table-sm">
                    <thead class="table-primary">
                      <tr>
                        <th>ID</th>
                        <th>Receipt #</th>
                        <th>Type</th>
                        <th>Party</th>
                        <th>Items</th>
                        <th>Qty</th>
                        <th>Stock</th>
                        <th>Total Amount</th>
                        <th>Discount</th>
                        <th>Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()):
                        $totalAmount = (float)$row['total_amount'];
                        $discountValue = (float)$row['discount_value'];
                        $finalAmount = $totalAmount - $discountValue;
                        $type = $row['type'];

                        // Determine values based on type
                        if ($type === 'purchase') {
                          $items = $row['purchase_items'] ?? 0;
                          $quantity = $row['purchase_quantity'] ?? 0;
                          $stockLeft = $row['purchase_stock_left'] ?? 0;
                          $stockPercentage = $quantity > 0 ? ($stockLeft / $quantity) * 100 : 0;
                          $partyName = $row['supplier_name'] ?? 'Multiple';
                          $isPurchase = true;
                        } else {
                          $items = $row['sale_items'] ?? 0;
                          $quantity = $row['sale_quantity'] ?? 0;
                          $stockLeft = 0;
                          $stockPercentage = 0;
                          $partyName = $row['buyer_name'] ?? 'Walk-in';
                          $isPurchase = false;
                        }

                        // Determine stock badge class for purchases
                        if ($isPurchase) {
                          if ($stockPercentage >= 50) {
                            $stockClass = 'stock-good';
                            $stockText = 'Good';
                          } elseif ($stockPercentage >= 25) {
                            $stockClass = 'stock-medium';
                            $stockText = 'Medium';
                          } elseif ($stockPercentage > 0) {
                            $stockClass = 'stock-low';
                            $stockText = 'Low';
                          } else {
                            $stockClass = 'bg-secondary';
                            $stockText = 'Empty';
                          }
                        }
                      ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td>
                            <span class="fw-bold"><?= htmlspecialchars($row['receipt_number']) ?></span>
                          </td>
                          <td>
                            <span class="badge receipt-type <?= $isPurchase ? 'type-purchase' : 'type-sale' ?>">
                              <?= strtoupper($type) ?>
                            </span>
                          </td>
                          <td class="customer-info" title="<?= htmlspecialchars($partyName) . ($isPurchase ? '' : ' ' . htmlspecialchars($row['buyer_phone'] ?? '')) ?>">
                            <div class="fw-bold"><?= htmlspecialchars($partyName) ?></div>
                            <?php if (!$isPurchase && !empty($row['buyer_phone'])): ?>
                              <div class="text-muted small"><?= htmlspecialchars($row['buyer_phone']) ?></div>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge bg-info"><?= $items ?> items</span>
                          </td>
                          <td>
                            <span class="fw-bold"><?= $quantity ?></span>
                          </td>
                          <td>
                            <?php if ($isPurchase): ?>
                              <span class="badge stock-badge <?= $stockClass ?>">
                                <?= $stockLeft ?> (<?= number_format($stockPercentage, 0) ?>%)
                                <small class="ms-1"><?= $stockText ?></small>
                              </span>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td class="amount-cell">
                            <div class="fw-bold">$<?= number_format($finalAmount, 2) ?></div>
                            <small class="text-muted">Gross: $<?= number_format($totalAmount, 2) ?></small>
                          </td>
                          <td>
                            <?php if ($discountValue > 0): ?>
                              <span class="text-danger">-$<?= number_format($discountValue, 2) ?></span>
                            <?php else: ?>
                              <span class="text-muted">$0.00</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="small"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                            <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                          </td>
                          <td>
                            <div class="btn-group btn-group-sm" role="group">
                              <?php if (can('view_receipt')): ?>
                                <a href="receipt.php?id=<?= $row['id'] ?>"
                                  class="btn btn-primary"
                                  title="View Receipt">
                                  <i class="bi bi-receipt"></i>
                                </a>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="11" class="text-end">
                          <strong>My Total:</strong> <?= $result->num_rows ?> receipts |
                          <strong>Purchase:</strong> <?= $statsResult['purchase_receipts'] ?? 0 ?> |
                          <strong>Sale:</strong> <?= $statsResult['sale_receipts'] ?? 0 ?> |
                          <strong>Amount:</strong> $<?= number_format($statsResult['total_amount'] ?? 0, 2) ?> |
                          <strong>Discount:</strong> $<?= number_format($statsResult['total_discount'] ?? 0, 2) ?>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-receipt-cutoff fs-1 d-block mb-2"></i>
              <h5>No receipts found</h5>
              <p class="mb-3">You haven't created any receipts yet</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- JS Dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="<?= $Project_URL ?>/js/adminlte.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      $('#myAllReceiptsTable').DataTable({
        paging: true,
        pageLength: 15,
        lengthChange: true,
        lengthMenu: [10, 15, 25, 50],
        ordering: true,
        order: [
          [0, 'desc']
        ], // Sort by ID descending
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
          search: "Search my receipts:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ receipts",
          infoEmpty: "No receipts to show",
          infoFiltered: "(filtered from _MAX_ total receipts)",
          zeroRecords: "No matching receipts found"
        },
        columnDefs: [{
            responsivePriority: 1,
            targets: 1
          }, // Receipt #
          {
            responsivePriority: 2,
            targets: 2
          }, // Type
          {
            responsivePriority: 3,
            targets: 7
          }, // Total Amount
          {
            responsivePriority: 4,
            targets: 10
          } // Actions
        ]
      });

      // Auto-hide messages after 3 seconds
      setTimeout(() => {
        const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
        if (msg) msg.remove();
      }, 3000);
    });
  </script>
</body>

</html>