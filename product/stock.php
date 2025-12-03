<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';

$conn = connectDB();

// Fetch stock overview
$sql = "
  SELECT 
    p.id,
    p.name,
    p.category_id,
    p.supplier_id,
    p.price,
    p.quantity_in_stock,
    c.name AS category_name,
    s.name AS supplier_name
  FROM product p
  LEFT JOIN category c ON p.category_id = c.id
  LEFT JOIN supplier s ON p.supplier_id = s.id
  ORDER BY p.name ASC 
";
$result = $conn->query($sql);

// Stock statistics
$totalProducts = $result->num_rows;
$totalStock = 0;
$lowStockCount = 0;
$lowStockThreshold = 5; // define low stock threshold

while ($row = $result->fetch_assoc()) {
  $totalStock += $row['quantity_in_stock'];
  if ($row['quantity_in_stock'] <= $lowStockThreshold) {
    $lowStockCount++;
  }
  $productsData[] = $row; // keep data for table
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock Overview | Sass Inventory Management System</title>
  <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css">

  <style>
    .low-stock {
      background-color: #f8d7da !important;
    }

    .stock-card {
      border-radius: 12px;
      padding: 20px;
      color: #fff;
    }

    .stock-card h3 {
      font-weight: 700;
    }

    .stock-card i {
      font-size: 2.5rem;
    }
  </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Header -->
    <?php include_once '../Inc/Navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once '../Inc/Sidebar.php'; ?>

    <main class="app-main">

      <!-- Page Header -->
      <div class="app-content-header py-3 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="mb-0">Stock Overview</h3>
          <a href="add.php" class="btn btn-sm btn-primary px-3 py-2">
            <i class="bi bi-plus me-1"></i> Add New Product
          </a>
        </div>
      </div>

      <div class="container-fluid mt-4">

        <!-- Stock Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="stock-card bg-primary d-flex align-items-center justify-content-between">
              <div>
                <h3><?= $totalProducts ?></h3>
                <p class="mb-0">Total Products</p>
              </div>
              <i class="bi bi-box-seam"></i>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stock-card bg-success d-flex align-items-center justify-content-between">
              <div>
                <h3><?= $totalStock ?></h3>
                <p class="mb-0">Total Stock Units</p>
              </div>
              <i class="bi bi-stack"></i>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stock-card bg-danger d-flex align-items-center justify-content-between">
              <div>
                <h3><?= $lowStockCount ?></h3>
                <p class="mb-0">Low Stock Products</p>
              </div>
              <i class="bi bi-exclamation-triangle"></i>
            </div>
          </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
          <table id="stockTable" class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productsData as $row): ?>
                <tr class="<?= ($row['quantity_in_stock'] <= $lowStockThreshold) ? 'low-stock' : '' ?>">
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= $row['category_name'] ?: "—" ?></td>
                  <td><?= $row['supplier_name'] ?: "—" ?></td>
                  <td><?= number_format($row['price'], 2) ?></td>
                  <td><?= $row['quantity_in_stock'] ?></td>
                  <td>
                    <div class="d-flex gap-1">
                      <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm flex-fill">
                        <i class="bi bi-pencil-square"></i> Edit
                      </a>
                      <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm flex-fill" onclick="return confirm('Delete this product?');">
                        <i class="bi bi-trash"></i> Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      </div>

    </main>

    <!-- Footer -->
    <?php include_once '../Inc/Footer.php'; ?>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
  <script src="./js/adminlte.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#stockTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
          orderable: false,
          targets: 6
        }] // Disable sorting on Actions
      });

      // Auto hide messages
      setTimeout(() => {
        ['successMsg', 'failMsg'].forEach(id => {
          const el = document.getElementById(id);
          if (el) {
            el.style.transition = "opacity 0.5s";
            el.style.opacity = "0";
            setTimeout(() => el.remove(), 500);
          }
        });
      }, 3000);
    });
  </script>

</body>

</html>