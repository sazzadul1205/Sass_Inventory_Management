<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

$conn = connectDB();

// Fetch purchases with product & supplier names
$sql = "
  SELECT 
    p.id,
    p.product_id,
    pr.name AS product_name,
    p.supplier_id,
    s.name AS supplier_name,
    p.quantity,
    p.purchase_price,
    p.purchase_date,
    p.created_at,
    p.updated_at
  FROM purchase p
  LEFT JOIN product pr ON p.product_id = pr.id
  LEFT JOIN supplier s ON p.supplier_id = s.id
  ORDER BY p.id DESC
";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchases | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <div class="app-wrapper">

    <?php include_once '../Inc/Navbar.php'; ?>
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0">All Purchases</h3>
          <a href="add.php" class="btn btn-sm btn-primary px-3 py-2">
            <i class="bi bi-plus me-1"></i> Add New Purchase
          </a>
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

      <div class="app-content-body mt-3">
        <div class="container-fluid">

          <?php if ($result->num_rows == 0): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              <h5>No purchases found</h5>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table id="purchaseTable" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-primary">
                  <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Purchase Price</th>
                    <th>Purchase Date</th>
                    <th>Created At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['id'] ?></td>
                      <td><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></td>
                      <td><?= htmlspecialchars($row['supplier_name'] ?? 'Unknown') ?></td>
                      <td><?= htmlspecialchars($row['quantity'] ?? '-') ?></td>
                      <td><?= htmlspecialchars(number_format($row['purchase_price'], 2) ?? '-') ?></td>
                      <td><?= !empty($row['purchase_date']) ? date('d M Y', strtotime($row['purchase_date'])) : '-' ?></td>
                      <td><?= !empty($row['created_at']) ? date('d M Y h:i A', strtotime($row['created_at'])) : '-' ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </main>

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

  <script>
    $(document).ready(function() {
      $('#usersTable').DataTable({
        pageLength: 10,
        lengthChange: false
      });
    });

    setTimeout(() => {
      const msg = document.getElementById('successMsg') || document.getElementById('failMsg');
      if (msg) msg.remove();
    }, 3000);
  </script>
</body>

</html>