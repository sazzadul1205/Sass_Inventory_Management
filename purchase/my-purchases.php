<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_my_purchases', '../index.php');

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
  <title>My Purchases | Sass Inventory Management System</title>
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

    .stock-badge {
      font-size: 0.7em;
    }

    .stock-full {
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

    .stock-empty {
      background-color: #f5f5f5 !important;
      color: #6c757d !important;
    }

    .btn-group-sm .btn {
      padding: 0.25rem 0.5rem;
    }
  </style>
</head>

<?php
$conn = connectDB();
$userId = $_SESSION['user_id'];

// Fetch my purchases with product and supplier details
$sql = "
    SELECT 
        p.*,
        pr.name AS product_name,
        s.name AS supplier_name,
        r.receipt_number,
        r.total_amount,
        r.discount_value,
        u.username AS purchaser_name
    FROM purchase p
    LEFT JOIN product pr ON p.product_id = pr.id
    LEFT JOIN supplier s ON p.supplier_id = s.id
    LEFT JOIN receipt r ON p.receipt_id = r.id
    LEFT JOIN user u ON p.purchased_by = u.id
    WHERE p.purchased_by = ?
    ORDER BY p.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Calculate my purchase statistics
$statsSql = "
    SELECT 
        COUNT(*) as total_purchases,
        SUM(p.quantity) as total_quantity,
        SUM(p.purchase_price * p.quantity) as total_amount,
        SUM(p.product_left) as total_stock_left
    FROM purchase p
    WHERE p.purchased_by = ?
";

$statsStmt = $conn->prepare($statsSql);
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

// Calculate stock percentage
$totalQuantity = $statsResult['total_quantity'] ?? 0;
$totalStockLeft = $statsResult['total_stock_left'] ?? 0;
$stockPercentage = $totalQuantity > 0 ? ($totalStockLeft / $totalQuantity) * 100 : 0;
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
          <h3 class="mb-0" style="font-weight: 800;">My Purchases</h3>

          <!-- Add Purchase Button -->
          <?php if (can('add_purchase')): ?>
            <a href="add.php" class="btn btn-sm btn-primary px-3 py-2" style="font-size: medium;">
              <i class="bi bi-plus me-1"></i> Add New Purchase
            </a>
          <?php endif; ?>
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
            <div class="card bg-primary text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Purchases</h6>
                    <h3 class="mb-0"><?= $statsResult['total_purchases'] ?? 0 ?></h3>
                  </div>
                  <i class="bi bi-cart-plus fs-1"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Quantity</h6>
                    <h3 class="mb-0"><?= $statsResult['total_quantity'] ?? 0 ?></h3>
                  </div>
                  <i class="bi bi-box-seam fs-1"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card bg-info text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Amount</h6>
                    <h3 class="mb-0">$<?= number_format($statsResult['total_amount'] ?? 0, 2) ?></h3>
                  </div>
                  <i class="bi bi-currency-dollar fs-1"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card <?= $stockPercentage >= 50 ? 'bg-warning' : ($stockPercentage > 0 ? 'bg-danger' : 'bg-secondary') ?> text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Stock Left</h6>
                    <h3 class="mb-0"><?= $statsResult['total_stock_left'] ?? 0 ?></h3>
                    <small class="opacity-75"><?= number_format($stockPercentage, 1) ?>% remaining</small>
                  </div>
                  <i class="bi bi-inboxes fs-1"></i>
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
                <h5 class="card-title mb-0">My Purchase History</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="myPurchasesTable" class="table table-bordered table-striped table-hover align-middle table-sm">
                    <thead class="table-primary">
                      <tr>
                        <th>ID</th>
                        <th>Receipt</th>
                        <th>Product</th>
                        <th>Supplier</th>
                        <th>Lot</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Stock Left</th>
                        <th>Purchase Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()):
                        $quantity = (int)$row['quantity'];
                        $productLeft = (int)$row['product_left'];
                        $purchasePrice = (float)$row['purchase_price'];
                        $stockPercentage = $quantity > 0 ? ($productLeft / $quantity) * 100 : 0;

                        // Determine stock badge class
                        if ($productLeft === 0) {
                          $stockClass = 'stock-empty';
                          $stockText = 'Sold Out';
                        } elseif ($stockPercentage <= 25) {
                          $stockClass = 'stock-low';
                          $stockText = 'Low';
                        } elseif ($stockPercentage <= 50) {
                          $stockClass = 'stock-medium';
                          $stockText = 'Medium';
                        } else {
                          $stockClass = 'stock-full';
                          $stockText = 'Good';
                        }
                      ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td>
                            <span class="badge bg-secondary" title="Receipt: <?= htmlspecialchars($row['receipt_number'] ?? 'N/A') ?>">
                              <?= !empty($row['receipt_number']) ? substr($row['receipt_number'], 0, 8) . '...' : 'N/A' ?>
                            </span>
                          </td>
                          <td><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></td>
                          <td><?= htmlspecialchars($row['supplier_name'] ?? 'Unknown') ?></td>
                          <td>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($row['lot'] ?? 'N/A') ?></span>
                          </td>
                          <td><?= $quantity ?></td>
                          <td>$<?= number_format($purchasePrice, 2) ?></td>
                          <td>
                            <span class="badge stock-badge <?= $stockClass ?>">
                              <?= $productLeft ?> (<?= number_format($stockPercentage, 0) ?>%)
                              <small class="ms-1"><?= $stockText ?></small>
                            </span>
                          </td>
                          <td><?= !empty($row['purchase_date']) ? date('d M Y', strtotime($row['purchase_date'])) : date('d M Y', strtotime($row['created_at'])) ?></td>
                          <td>
                            <div class="btn-group btn-group-sm" role="group">
                              <?php if (can('view_receipt') && !empty($row['receipt_id'])): ?>
                                <a href="receipt.php?id=<?= $row['receipt_id'] ?>"
                                  class="btn btn-primary"
                                  title="View Receipt">
                                  <i class="bi bi-receipt"></i>
                                </a>
                              <?php endif; ?>

                              <?php if (can('product_return') && $productLeft > 0): ?>
                                <a href="purchase_return.php?lot=<?= urlencode($row['lot']) ?>&product_id=<?= $row['product_id'] ?>"
                                  class="btn btn-warning"
                                  title="Return Item">
                                  <i class="bi bi-arrow-return-left"></i>
                                </a>
                              <?php elseif (can('product_return')): ?>
                                <button class="btn btn-secondary" disabled
                                  title="No stock left to return">
                                  <i class="bi bi-arrow-return-left"></i>
                                </button>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="10" class="text-end">
                          <strong>My Total:</strong> <?= $result->num_rows ?> purchases |
                          <strong>Total Quantity:</strong> <?= $statsResult['total_quantity'] ?? 0 ?> units |
                          <strong>Stock Left:</strong> <?= $statsResult['total_stock_left'] ?? 0 ?> units |
                          <strong>Total Amount:</strong> $<?= number_format($statsResult['total_amount'] ?? 0, 2) ?>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-cart-plus fs-1 d-block mb-2"></i>
              <h5>No purchases found</h5>
              <p class="mb-3">You haven't made any purchases yet</p>
              <?php if (can('add_purchase')): ?>
                <a href="add.php" class="btn btn-primary">
                  <i class="bi bi-plus me-1"></i> Make Your First Purchase
                </a>
              <?php endif; ?>
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
      $('#myPurchasesTable').DataTable({
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
          search: "Search my purchases:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ purchases",
          infoEmpty: "No purchases to show",
          infoFiltered: "(filtered from _MAX_ total purchases)",
          zeroRecords: "No matching purchases found"
        },
        columnDefs: [{
            responsivePriority: 1,
            targets: 0
          }, // ID
          {
            responsivePriority: 2,
            targets: 2
          }, // Product
          {
            responsivePriority: 3,
            targets: 5
          }, // Quantity
          {
            responsivePriority: 4,
            targets: 7
          }, // Stock Left
          {
            responsivePriority: 5,
            targets: 9
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