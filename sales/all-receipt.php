<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_all_sales_receipts', '../index.php');

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
  <title>All Sales Receipts | Sass Inventory Management System</title>
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
      max-width: 150px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>
</head>

<?php
$conn = connectDB();

// Fetch all sales receipts with enhanced information
$sql = "
    SELECT 
        r.*,
        u.username AS created_by_name,
        COUNT(s.id) AS num_products,
        SUM(s.quantity) AS total_quantity,
        MAX(s.buyer_name) AS buyer_name,
        MAX(s.buyer_phone) AS buyer_phone,
        MIN(s.sale_date) AS sale_date
    FROM receipt r
    LEFT JOIN user u ON r.created_by = u.id
    LEFT JOIN sale s ON r.id = s.receipt_id
    WHERE r.type = 'sale'
    GROUP BY r.id
    ORDER BY r.id DESC
";

$result = $conn->query($sql);

// Get total statistics
$statsSql = "
    SELECT 
        COUNT(*) as total_receipts,
        SUM(r.total_amount) as total_amount,
        SUM(r.discount_value) as total_discount,
        SUM(s.quantity) as total_quantity
    FROM receipt r
    LEFT JOIN sale s ON r.id = s.receipt_id
    WHERE r.type = 'sale'
    GROUP BY r.id
";
$statsQuery = $conn->query($statsSql);
$totalReceipts = 0;
$totalAmount = 0;
$totalDiscount = 0;
$totalQuantity = 0;

while ($row = $statsQuery->fetch_assoc()) {
  $totalReceipts += $row['total_receipts'];
  $totalAmount += $row['total_amount'];
  $totalDiscount += $row['total_discount'];
  $totalQuantity += $row['total_quantity'];
}
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
          <h3 class="mb-0" style="font-weight: 800;">All Sales Receipts</h3>

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

      <!-- Table -->
      <div class="app-content-body mt-3">
        <div class="container-fluid">
          <?php if ($result->num_rows > 0): ?>
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">Sales Receipts List</h5>
                <div class="text-muted small mt-1">
                  Total: <?= $totalReceipts ?> receipts |
                  Amount: $<?= number_format($totalAmount, 2) ?> |
                  Quantity: <?= $totalQuantity ?> units |
                  Discount: $<?= number_format($totalDiscount, 2) ?>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="salesReceiptsTable" class="table table-bordered table-striped table-hover align-middle table-sm">
                    <thead class="table-primary">
                      <tr>
                        <th>ID</th>
                        <th>Receipt #</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Qty</th>
                        <th>Total Amount</th>
                        <th>Discount</th>
                        <th>Sold By</th>
                        <th>Sale Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()):
                        $totalAmount = (float)$row['total_amount'];
                        $discountValue = (float)$row['discount_value'];
                        $finalAmount = $totalAmount - $discountValue;
                      ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td>
                            <span class="fw-bold"><?= htmlspecialchars($row['receipt_number']) ?></span>
                          </td>
                          <td>
                            <span class="badge receipt-type type-sale">
                              SALE
                            </span>
                          </td>
                          <td class="customer-info" title="<?= htmlspecialchars($row['buyer_name'] ?? '') . ' ' . htmlspecialchars($row['buyer_phone'] ?? '') ?>">
                            <?php if (!empty($row['buyer_name'])): ?>
                              <div class="fw-bold"><?= htmlspecialchars($row['buyer_name']) ?></div>
                              <?php if (!empty($row['buyer_phone'])): ?>
                                <div class="text-muted small"><?= htmlspecialchars($row['buyer_phone']) ?></div>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="text-muted">Walk-in</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge bg-info"><?= $row['num_products'] ?> items</span>
                          </td>
                          <td>
                            <span class="fw-bold"><?= $row['total_quantity'] ?></span>
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
                            <div class="small"><?= htmlspecialchars($row['created_by_name']) ?></div>
                            <small class="text-muted">ID: <?= $row['created_by'] ?></small>
                          </td>
                          <td>
                            <?php if (!empty($row['sale_date'])): ?>
                              <div class="small"><?= date('d M Y', strtotime($row['sale_date'])) ?></div>
                            <?php else: ?>
                              <div class="small"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                            <?php endif; ?>
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

                              <?php if (can('delete_sale')): ?>
                                <button type="button"
                                  class="btn btn-danger"
                                  onclick="confirmDelete(<?= $row['id'] ?>)"
                                  title="Delete Receipt"
                                  <?= !can('delete_sale') ? 'disabled' : '' ?>>
                                  <i class="bi bi-trash"></i>
                                </button>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="11" class="text-end">
                          <strong>Total Sales:</strong> <?= $result->num_rows ?> receipts |
                          <strong>Total Amount:</strong> $<?= number_format($totalAmount, 2) ?> |
                          <strong>Total Discount:</strong> $<?= number_format($totalDiscount, 2) ?> |
                          <strong>Total Quantity:</strong> <?= $totalQuantity ?> units
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
              <h5>No sales receipts found</h5>
              <p class="mb-3">Start by creating your first sales receipt</p>
              <?php if (can('add_sale')): ?>
                <a href="add.php" class="btn btn-primary">
                  <i class="bi bi-plus me-1"></i> Create First Sale
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

  <!-- SweetAlert2 for confirmation dialogs -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Custom JS -->
  <script>
    $(document).ready(function() {
      $('#salesReceiptsTable').DataTable({
        paging: true,
        pageLength: 20,
        lengthChange: true,
        lengthMenu: [10, 20, 50, 100],
        ordering: true,
        order: [
          [0, 'desc']
        ], // Sort by ID descending
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
          search: "Search sales receipts:",
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
            targets: 6
          }, // Total Amount
          {
            responsivePriority: 3,
            targets: 9
          }, // Date
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

    // Delete confirmation function
    function confirmDelete(receiptId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "This sales receipt and all associated items will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to delete script
          window.location.href = `delete_receipt.php?id=${receiptId}`;
        }
      });
    }
  </script>
</body>

</html>