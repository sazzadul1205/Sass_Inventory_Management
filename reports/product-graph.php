<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Connect to DB
$conn = connectDB();

// Fetch stock data
$sql = "SELECT * FROM stock_report";

$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Stock Report | Sass Inventory</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</head>

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
          <h3 class="mb-0 " style="font-weight: 800;">Stock Report</h3>
        </div>
      </div>

      <!-- Table -->
      <div class="app-content-body mt-3">
        <div class="table-responsive container-fluid">
          <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
              <table id="stockTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                  <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Supplier</th>
                    <th>Purchase Price</th>
                    <th>Total Purchased Qty</th>
                    <th>Total Sold Qty</th>
                    <th>Current Stock</th>
                    <th>Inventory Value</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $index => $prod):
                    $inventoryValue = $prod['current_stock'] * $prod['purchase_price'];
                  ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars($prod['product_name']) ?></td>
                      <td><?= htmlspecialchars($prod['supplier_name']) ?></td>
                      <td><?= number_format($prod['purchase_price'], 2) ?></td>
                      <td><?= $prod['total_purchased_qty'] ?></td>
                      <td><?= $prod['total_sold_qty'] ?></td>
                      <td><?= $prod['current_stock'] ?></td>
                      <td><?= number_format($inventoryValue, 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <h5>No purchases found</h5>
              </div>
            <?php endif; ?>
            </div>
        </div>
      </div>
    </main>
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
      $('#stockTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false
      });
    });
  </script>
</body>

</html>