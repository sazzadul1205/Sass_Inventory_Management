<?php
// Include the conflict-free auth guard
include_once __DIR__ . '/../config/auth_guard.php';

// Require the user to have 'view_roles' permission
// Unauthorized users will be redirected to the project root index.php
requirePermission('view_all_sales', '../index.php');

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
  <title>All Product Sales | Sass Inventory Management System</title>
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
  </style>
</head>

<?php
$conn = connectDB();

// Fetch all sales with product and user details
$sql = "
    SELECT 
        s.*,
        p.name AS product_name,
        u.username AS seller_name,
        r.receipt_number,
        r.total_amount,
        r.discount_value
    FROM sale s
    LEFT JOIN product p ON s.product_id = p.id
    LEFT JOIN user u ON s.sold_by = u.id
    LEFT JOIN receipt r ON s.receipt_id = r.id
    ORDER BY s.id DESC
";
$result = $conn->query($sql);
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
          <h3 class="mb-0" style="font-weight: 800;">All Sales</h3>

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
              <div class="card-body">
                <div class="table-responsive">
                  <table id="salesTable" class="table table-bordered table-striped table-hover align-middle table-sm">
                    <thead class="table-primary">
                      <tr>
                        <th>ID</th>
                        <th>Receipt</th>
                        <th>Product</th>
                        <th>Lot</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>VAT (%)</th>
                        <th>Total</th>
                        <th>Customer</th>
                        <th>Sale Date</th>
                        <th>Sold By</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($row = $result->fetch_assoc()):
                        // Calculate totals
                        $unitPrice = (float)$row['sale_price'];
                        $quantity = (int)$row['quantity'];
                        $vatPercent = (float)($row['vat_percent'] ?? 0);
                        $subtotal = $unitPrice * $quantity;
                        $vatAmount = $subtotal * ($vatPercent / 100);
                        $total = $subtotal + $vatAmount;
                      ?>
                        <tr>
                          <td><?= $row['id'] ?></td>
                          <td>
                            <span class="badge bg-secondary" title="Receipt: <?= htmlspecialchars($row['receipt_number'] ?? 'N/A') ?>">
                              <?= !empty($row['receipt_number']) ? substr($row['receipt_number'], 0, 10) . '...' : 'N/A' ?>
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
                            <span class="fw-bold"><?= number_format($total, 2) ?></span>
                            <?php if ($vatPercent > 0): ?>
                              <small class="text-muted d-block">Inc. VAT: <?= number_format($vatAmount, 2) ?></small>
                            <?php endif; ?>
                          </td>
                          <td class="customer-info" title="<?= htmlspecialchars($row['buyer_name'] ?? '') ?>">
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
                          <td><?= htmlspecialchars($row['seller_name'] ?? '-') ?></td>
                          <td>
                            <div class="btn-group btn-group-sm" role="group">
                              <?php if (can('view_receipt') && !empty($row['receipt_id'])): ?>
                                <a href="receipt.php?id=<?= $row['receipt_id'] ?>"
                                  class="btn btn-primary"
                                  title="View Receipt">
                                  <i class="bi bi-receipt"></i>
                                </a>
                              <?php endif; ?>

                              <?php if (can('delete_sale')): ?>
                                <button type="button"
                                  class="btn btn-danger"
                                  onclick="confirmDelete(<?= $row['id'] ?>)"
                                  title="Delete Sale"
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
                        <td colspan="12" class="text-end">
                          <strong>Total Sales:</strong> <?= $result->num_rows ?> transactions
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No sales found</h5>
              <p class="mb-3">Start by adding your first sale</p>
              <?php if (can('add_sale')): ?>
                <a href="add.php" class="btn btn-primary">
                  <i class="bi bi-plus me-1"></i> Add New Sale
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
      $('#salesTable').DataTable({
        paging: true,
        pageLength: 25,
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        order: [
          [0, 'desc']
        ], // Sort by ID descending by default
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
          search: "Search sales:",
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
            targets: 7
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

    // Delete confirmation function
    function confirmDelete(saleId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "This sale will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirect to delete script
          window.location.href = `delete.php?id=${saleId}`;
        }
      });
    }
  </script>
</body>

</html>