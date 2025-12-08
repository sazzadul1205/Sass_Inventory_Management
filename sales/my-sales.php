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
  <title>My Sale's | Sass Inventory Management System</title>
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
  </style>
</head>

<?php
$conn = connectDB();

$userId = $_SESSION['user_id'];

// Fetch all product_with_details
$sql = "SELECT * 
        FROM sale_details
        WHERE sold_by = '$userId'
        ORDER BY id DESC";
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
          <h3 class="mb-0 " style="font-weight: 800;">My Sales</h3>

          <!-- Add User Button -->
          <?php if (can('add_sale')): ?>
            <a href="add.php" class="btn btn-sm btn-primary px-3 py-2" style=" font-size: medium; ">
              <i class="bi bi-plus me-1"></i> Add New Sales
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
        <div class="table-responsive container-fluid">
          <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
              <table id="purchaseTable" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Sale Price</th>
                    <th>Sale Date</th>
                    <th>Sold By</th>
                    <th>View Receipt</th>
                  </tr>
                </thead>

                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['id'] ?></td>
                      <td><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></td>
                      <td><?= htmlspecialchars($row['quantity'] ?? '-') ?></td>
                      <td><?= !empty($row['sale_price']) ? number_format($row['sale_price'], 2) : '-' ?></td>
                      <td><?= !empty($row['sale_date']) ? date('d M Y', strtotime($row['sale_date'])) : '-' ?></td>
                      <td><?= htmlspecialchars($row['sold_by_name'] ?? '-') ?></td>
                      <td>
                        <?php if (can('view_receipt')): ?>
                          <?php if (!empty($row['receipt_id'])): ?>
                            <a href="receipt.php?id=<?= $row['receipt_id'] ?>" class="btn btn-sm btn-primary w-100">
                              <i class="bi bi-receipt"></i> Receipt
                            </a>
                          <?php endif; ?>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>

              </table>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No purchases found</h5>
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
      $('#purchaseTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false
      });
    });

    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.remove();
    }, 3000);
  </script>
</body>

</html>