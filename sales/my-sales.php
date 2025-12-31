<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_my_sales', '../index.php');

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
  <title>My Sales | Sass Inventory Management System</title>
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

    .customer-info {
      max-width: 150px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .profit-badge {
      font-size: 0.7em;
    }

    .profit-positive {
      color: #198754;
    }

    .profit-negative {
      color: #dc3545;
    }
  </style>
</head>

<?php
$conn = connectDB();
$userId = $_SESSION['user_id'];

// Fetch my sales with product and user details
$sql = "
    SELECT 
        s.*,
        p.name AS product_name,
        p.cost_price,
        u.username AS seller_name,
        r.receipt_number,
        r.total_amount,
        r.discount_value
    FROM sale s
    LEFT JOIN product p ON s.product_id = p.id
    LEFT JOIN user u ON s.sold_by = u.id
    LEFT JOIN receipt r ON s.receipt_id = r.id
    WHERE s.sold_by = ?
    ORDER BY s.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Calculate my total sales
$totalSalesSql = "
    SELECT 
        COUNT(*) as total_sales,
        SUM(s.quantity) as total_quantity,
        SUM(s.sale_price * s.quantity) as total_amount
    FROM sale s
    WHERE s.sold_by = ?
";
$totalStmt = $conn->prepare($totalSalesSql);
$totalStmt->bind_param("i", $userId);
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$totalStmt->close();
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
          <h3 class="mb-0" style="font-weight: 800;">My Sales</h3>

          <!-- Add Sale Button -->
          <?php if (can('add_sale')): ?>
            <a href="add.php" class="btn btn-sm btn-primary px-3 py-2" style="font-size: medium;">
              <i class="bi bi-plus me-1"></i> Add New Sale
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
          <div class="col-md-4">
            <div class="card bg-primary text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Sales</h6>
                    <h3 class="mb-0"><?= $totalResult['total_sales'] ?? 0 ?></h3>
                  </div>
                  <i class="bi bi-cart-check fs-1"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Quantity</h6>
                    <h3 class="mb-0"><?= $totalResult['total_quantity'] ?? 0 ?></h3>
                  </div>
                  <i class="bi bi-box-seam fs-1"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-info text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="card-title mb-1">Total Amount</h6>
                    <h3 class="mb-0">$<?= number_format($totalResult['total_amount'] ?? 0, 2) ?></h3>
                  </div>
                  <i class="bi bi-currency-dollar fs-1"></i>
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
                <h5 class="card-title mb-0">My Sales History</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="mySalesTable" class="table table-bordered table-striped table-hover align-middle table-sm">
                    <thead class="table-primary">
                      <tr>
                        <th>ID</th>
                        <th>Receipt</th>
                        <th>Product</th>
                        <th>Lot</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>VAT</th>
                        <th>Profit</th>
                        <th>Total</th>
                        <th>Customer</th>
                        <th>Sale Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()):
                        // Calculate values
                        $unitPrice = (float)$row['sale_price'];
                        $quantity = (int)$row['quantity'];
                        $costPrice = (float)($row['cost_price'] ?? 0);
                        $vatPercent = (float)($row['vat_percent'] ?? 0);

                        $subtotal = $unitPrice * $quantity;
                        $vatAmount = $subtotal * ($vatPercent / 100);
                        $total = $subtotal + $vatAmount;

                        // Calculate profit
                        $costTotal = $costPrice * $quantity;
                        $profit = $total - $costTotal;
                        $profitPercent = $costTotal > 0 ? ($profit / $costTotal) * 100 : 0;
                      ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td>
                            <span class="badge bg-secondary" title="Receipt: <?= htmlspecialchars($row['receipt_number'] ?? 'N/A') ?>">
                              <?= !empty($row['receipt_number']) ? substr($row['receipt_number'], 0, 8) . '...' : 'N/A' ?>
                            </span>
                          </td>
                          <td><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></td>
                          <td>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($row['lot'] ?? 'N/A') ?></span>
                          </td>
                          <td><?= $quantity ?></td>
                          <td><?= number_format($unitPrice, 2) ?></td>
                          <td>
                            <span class="badge bg-light text-dark"><?= number_format($vatPercent, 1) ?>%</span>
                          </td>
                          <td>
                            <?php if ($profit != 0): ?>
                              <span class="profit-badge <?= $profit > 0 ? 'profit-positive' : 'profit-negative' ?>">
                                <?= $profit > 0 ? '+' : '' ?><?= number_format($profit, 2) ?>
                                <small>(<?= $profit > 0 ? '+' : '' ?><?= number_format($profitPercent, 1) ?>%)</small>
                              </span>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="fw-bold"><?= number_format($total, 2) ?></span>
                          </td>
                          <td class="customer-info" title="<?= htmlspecialchars($row['buyer_name'] ?? '') . ' ' . htmlspecialchars($row['buyer_phone'] ?? '') ?>">
                            <?php if (!empty($row['buyer_name'])): ?>
                              <div><strong><?= htmlspecialchars($row['buyer_name']) ?></strong></div>
                              <?php if (!empty($row['buyer_phone'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars($row['buyer_phone']) ?></div>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="text-muted">Walk-in</span>
                            <?php endif; ?>
                          </td>
                          <td><?= !empty($row['sale_date']) ? date('d M Y', strtotime($row['sale_date'])) : date('d M Y', strtotime($row['created_at'])) ?></td>
                          <td>
                            <div class="btn-group btn-group-sm" role="group">
                              <?php if (can('view_receipt') && !empty($row['receipt_id'])): ?>
                                <a href="receipt.php?id=<?= $row['receipt_id'] ?>"
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
                        <td colspan="12" class="text-end">
                          <strong>My Total:</strong> <?= $result->num_rows ?> sales |
                          <strong>Quantity:</strong> <?= $totalResult['total_quantity'] ?? 0 ?> units |
                          <strong>Amount:</strong> $<?= number_format($totalResult['total_amount'] ?? 0, 2) ?>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
              <h5>No sales found</h5>
              <p class="mb-3">You haven't made any sales yet</p>
              <?php if (can('add_sale')): ?>
                <a href="add.php" class="btn btn-primary">
                  <i class="bi bi-plus me-1"></i> Make Your First Sale
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
      $('#mySalesTable').DataTable({
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
          search: "Search my sales:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ sales",
          infoEmpty: "No sales to show",
          infoFiltered: "(filtered from _MAX_ total sales)",
          zeroRecords: "No matching sales found"
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
            targets: 4
          }, // Quantity
          {
            responsivePriority: 4,
            targets: 8
          }, // Total
          {
            responsivePriority: 5,
            targets: 11
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